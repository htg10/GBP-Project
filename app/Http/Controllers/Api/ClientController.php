<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\GbpLocation;
use App\Models\Integration;
use App\Models\Review;
use App\Services\GoogleGbpProvider;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $aid = $request->user()->agency_id;
        $clientId = $request->user()->client_id;

        $clients = Client::where('agency_id', $aid)
            ->when($clientId, fn ($q) => $q->where('id', $clientId))
            ->withCount(['leads', 'locations'])
            ->latest()
            ->get();

        return response()->json($clients->map(fn ($c) => [
            'id' => $c->id,
            'name' => $c->name,
            'email' => $c->email,
            'phone' => $c->phone,
            'industry' => $c->industry,
            'leads_count' => $c->leads_count,
            'locations_count' => $c->locations_count,
        ]));
    }

    public function show(Request $request, Client $client)
    {
        $this->authorizeClient($request, $client);
        $client->load('locations', 'leads');

        $googleIntegration = Integration::where('client_id', $client->id)
            ->where('provider', 'GOOGLE_GBP')->first();
        $metaIntegration = Integration::where('client_id', $client->id)
            ->where('provider', 'META_GRAPH')->first();

        $locationIds = $client->locations->pluck('id');
        $reviews = Review::whereIn('gbp_location_id', $locationIds)->get();

        $stats = [
            'total' => $reviews->count(),
            'avg' => $reviews->count() ? round($reviews->avg('star_rating'), 1) : 0,
            'replied' => $reviews->whereNotNull('reply_text')->count(),
            'pending' => $reviews->whereNull('reply_text')->count(),
        ];

        $starDist = [];
        for ($i = 1; $i <= 5; $i++) {
            $starDist[$i] = $reviews->where('star_rating', $i)->count();
        }

        return response()->json([
            'client' => [
                'id' => $client->id,
                'name' => $client->name,
                'email' => $client->email,
                'phone' => $client->phone,
                'industry' => $client->industry,
            ],
            'google_connected' => $googleIntegration && $googleIntegration->access_token,
            'meta_connected' => $metaIntegration && $metaIntegration->access_token,
            'locations' => $client->locations->map(fn ($l) => [
                'id' => $l->id,
                'title' => $l->title,
                'address' => $l->address,
                'google_name' => $l->google_name,
            ]),
            'stats' => $stats,
            'star_distribution' => $starDist,
            'recent_reviews' => Review::whereIn('gbp_location_id', $locationIds)
                ->orderByDesc('review_time')->limit(5)->get()
                ->map(fn ($r) => [
                    'id' => $r->id,
                    'reviewer_name' => $r->reviewer_name,
                    'star_rating' => $r->star_rating,
                    'comment' => $r->comment,
                    'review_time' => $r->review_time,
                ]),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'industry' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email',
        ]);

        $data['agency_id'] = $request->user()->agency_id;
        $client = Client::create($data);

        return response()->json($client, 201);
    }

    public function update(Request $request, Client $client)
    {
        $this->authorizeClient($request, $client);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'industry' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email',
        ]);

        $client->update($data);

        return response()->json($client);
    }

    public function destroy(Request $request, Client $client)
    {
        $this->authorizeClient($request, $client);
        $client->delete();

        return response()->json(['success' => true]);
    }

    public function storeLocation(Request $request, Client $client)
    {
        $this->authorizeClient($request, $client);

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'google_name' => 'nullable|string|max:255',
        ]);

        if (empty($data['google_name'])) {
            $data['google_name'] = 'accounts/manual/locations/' . uniqid();
        }

        $location = GbpLocation::create([
            'agency_id' => $request->user()->agency_id,
            'client_id' => $client->id,
            'google_name' => $data['google_name'],
            'title' => $data['title'],
            'address' => $data['address'] ?? null,
        ]);

        return response()->json($location, 201);
    }

    public function destroyLocation(Request $request, Client $client, GbpLocation $location)
    {
        $this->authorizeClient($request, $client);
        abort_unless($location->client_id === $client->id, 403);

        $location->delete();

        return response()->json(['success' => true]);
    }

    public function importLocations(Request $request, Client $client, GoogleGbpProvider $gbp)
    {
        $this->authorizeClient($request, $client);

        $integration = Integration::where('client_id', $client->id)
            ->where('provider', 'GOOGLE_GBP')
            ->whereNotNull('access_token')
            ->first();

        if (! $integration) {
            return response()->json(['message' => 'Connect Google Business Profile first.'], 422);
        }

        $accounts = $gbp->listAccounts($integration);
        $imported = 0;

        foreach ($accounts as $account) {
            $locations = $gbp->listLocations($integration, $account['name']);
            foreach ($locations as $loc) {
                GbpLocation::updateOrCreate(
                    ['google_name' => $loc['google_name']],
                    [
                        'client_id' => $client->id,
                        'title' => $loc['title'] ?? $loc['google_name'],
                        'address' => $loc['address'] ?? null,
                    ]
                );
                $imported++;
            }
        }

        GbpLocation::where('client_id', $client->id)
            ->where('google_name', 'accounts/123/locations/456')
            ->delete();

        return response()->json(['success' => true, 'imported' => $imported]);
    }

    public function disconnectGoogle(Request $request, Client $client)
    {
        $this->authorizeClient($request, $client);
        Integration::where('client_id', $client->id)->where('provider', 'GOOGLE_GBP')->delete();

        return response()->json(['success' => true]);
    }

    public function disconnectMeta(Request $request, Client $client)
    {
        $this->authorizeClient($request, $client);
        Integration::where('client_id', $client->id)->where('provider', 'META_GRAPH')->delete();

        return response()->json(['success' => true]);
    }

    private function authorizeClient(Request $request, Client $client): void
    {
        abort_unless($client->agency_id === $request->user()->agency_id, 403);
        $scoped = $request->user()->client_id;
        abort_if($scoped && $client->id !== $scoped, 403);
    }
}
