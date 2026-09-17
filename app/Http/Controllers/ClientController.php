<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\GbpLocation;
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

        return view('dashboard.clients', compact('clients'));
    }

    public function show(Request $request, Client $client)
    {
        $this->authorizeClient($request, $client);
        $client->load('locations', 'leads');
        $googleIntegration = \App\Models\Integration::where('client_id', $client->id)
            ->where('provider', 'GOOGLE_GBP')->first();
        $metaIntegration = \App\Models\Integration::where('client_id', $client->id)
            ->where('provider', 'META_GRAPH')->first();

        // Per-account (individual dashboard) review stats across all its locations.
        $locationIds = $client->locations->pluck('id');
        $reviews = \App\Models\Review::whereIn('gbp_location_id', $locationIds)->get();

        $total = $reviews->count();
        $stats = [
            'total'   => $total,
            'avg'     => $total ? round($reviews->avg('star_rating'), 1) : 0,
            'replied' => $reviews->whereNotNull('reply_text')->count(),
            'pending' => $reviews->whereNull('reply_text')->count(),
        ];

        $starDist = [];
        for ($s = 5; $s >= 1; $s--) {
            $starDist[$s] = $reviews->where('star_rating', $s)->count();
        }

        $recentReviews = $reviews->sortByDesc('review_time')->take(5)->values();

        return view('dashboard.client-show', compact(
            'client', 'googleIntegration', 'metaIntegration',
            'stats', 'starDist', 'recentReviews'
        ));
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
        Client::create($data);

        return back()->with('success', 'Client added.');
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

        return back()->with('success', 'Client updated.');
    }

    public function destroy(Request $request, Client $client)
    {
        $this->authorizeClient($request, $client);
        $client->delete();

        return redirect()->route('clients')->with('success', 'Client deleted.');
    }

    // ---- GBP location under a client ----

    public function storeLocation(Request $request, Client $client)
    {
        $this->authorizeClient($request, $client);
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'google_name' => 'nullable|string|max:255',
        ]);

        // If no google_name given, generate a mock one (real value comes from OAuth later).
        $data['google_name'] = $data['google_name']
            ?: 'accounts/manual/locations/'.uniqid();
        $data['client_id'] = $client->id;

        GbpLocation::create($data);

        return back()->with('success', 'Location added.');
    }

    public function destroyLocation(Request $request, Client $client, GbpLocation $location)
    {
        $this->authorizeClient($request, $client);
        abort_unless($location->client_id === $client->id, 403);
        $location->delete();

        return back()->with('success', 'Location removed.');
    }

    /**
     * Pull the client's REAL locations from Google and save them.
     * Requires the client to be connected (GOOGLE_GBP integration with token).
     */
    public function importLocations(Request $request, Client $client, \App\Services\GoogleGbpProvider $gbp)
    {
        $this->authorizeClient($request, $client);

        $integration = \App\Models\Integration::where('client_id', $client->id)
            ->where('provider', 'GOOGLE_GBP')
            ->whereNotNull('access_token')
            ->first();

        if (! $integration) {
            return back()->with('error', 'Connect this client to Google first.');
        }

        $accounts = $gbp->listAccounts($integration);
        if (empty($accounts)) {
            return back()->with('error', 'No Google Business accounts found — or the Business Profile API is not approved yet. Check storage/logs/laravel.log for details.');
        }

        $imported = 0;
        foreach ($accounts as $account) {
            $accountName = $account['name'] ?? null; // e.g. accounts/123
            if (! $accountName) continue;

            $locations = $gbp->listLocations($integration, $accountName);
            foreach ($locations as $loc) {
                GbpLocation::updateOrCreate(
                    ['client_id' => $client->id, 'google_name' => $loc['google_name']],
                    ['title' => $loc['title'], 'address' => $loc['address']]
                );
                $imported++;
            }
        }

        if ($imported === 0) {
            return back()->with('error', 'Connected, but Google returned no locations. This usually means the Business Profile API access is still pending approval. See storage/logs/laravel.log.');
        }

        // Remove the seeded demo location so real reviews sync cleanly.
        GbpLocation::where('client_id', $client->id)
            ->where('google_name', 'accounts/123/locations/456')
            ->delete();

        return back()->with('success', "Imported {$imported} real location(s) from Google. Now go to Reviews → Sync.");
    }

    private function authorizeClient(Request $request, Client $client): void
    {
        abort_unless($client->agency_id === $request->user()->agency_id, 403);
        // Client-bound users may only touch their own client.
        $scoped = $request->user()->client_id;
        abort_if($scoped && $client->id !== $scoped, 403, 'You can only access your own business.');
    }
}
