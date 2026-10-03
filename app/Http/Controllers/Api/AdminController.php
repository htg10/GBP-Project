<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Agency;
use App\Models\Client;
use App\Models\CreditPackage;
use App\Models\Plan;
use App\Models\Review;
use App\Models\Subscription;
use App\Models\User;
use App\Services\CreditService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function overview(Request $request)
    {
        $stats = [
            'agencies' => Agency::count(),
            'users' => User::count(),
            'clients' => Client::count(),
            'reviews' => Review::count(),
        ];

        $users = User::with('agency')->latest()->take(50)->get()->map(fn ($u) => [
            'id' => $u->id,
            'name' => $u->name,
            'email' => $u->email,
            'role' => $u->role,
            'agency' => $u->agency?->name,
            'created_at' => $u->created_at,
        ]);

        return response()->json([
            'stats' => $stats,
            'recent_users' => $users,
        ]);
    }

    public function users()
    {
        $users = User::with(['agency', 'client'])->latest()->get();
        $subscriptions = Subscription::all()->keyBy('agency_id');

        return response()->json($users->map(fn ($u) => [
            'id' => $u->id,
            'name' => $u->name,
            'email' => $u->email,
            'role' => $u->role,
            'agency_id' => $u->agency_id,
            'agency' => $u->agency?->name,
            'client_id' => $u->client_id,
            'client' => $u->client?->name,
            'plan' => $subscriptions[$u->agency_id]->plan ?? null,
            'created_at' => $u->created_at,
        ]));
    }

    public function storeUser(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'password' => 'required|string|min:8',
            'role' => 'required|in:SUPER_ADMIN,CLIENT_OWNER',
            'plan_id' => 'nullable|exists:plans,id',
        ]);

        if (User::where('email', $data['email'])->exists()) {
            return response()->json(['message' => 'Email already in use.'], 422);
        }

        $data['agency_id'] = $request->user()->agency_id;

        if ($data['role'] === 'CLIENT_OWNER') {
            $client = Client::create(['agency_id' => $data['agency_id'], 'name' => $data['name'], 'email' => $data['email']]);
            $data['client_id'] = $client->id;
        }

        $user = User::create($data);

        if (! empty($data['plan_id'])) {
            $this->assignPlan($user, $data['plan_id']);
        }

        return response()->json(['id' => $user->id, 'name' => $user->name, 'email' => $user->email, 'role' => $user->role], 201);
    }

    public function updateUser(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'role' => 'required|in:SUPER_ADMIN,CLIENT_OWNER',
            'password' => 'nullable|string|min:8',
            'plan_id' => 'nullable|exists:plans,id',
        ]);

        if (User::where('email', $data['email'])->where('id', '!=', $user->id)->exists()) {
            return response()->json(['message' => 'Email already in use.'], 422);
        }

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->role = $data['role'];

        if ($data['role'] === 'CLIENT_OWNER' && ! $user->client_id) {
            $client = Client::create(['agency_id' => $user->agency_id, 'name' => $data['name'], 'email' => $data['email']]);
            $user->client_id = $client->id;
        }

        if (! empty($data['password'])) {
            $user->password = $data['password'];
        }
        $user->save();

        if (array_key_exists('plan_id', $data)) {
            $this->assignPlan($user, $data['plan_id']);
        }

        return response()->json(['success' => true]);
    }

    public function destroyUser(Request $request, User $user)
    {
        if ($user->role === 'SUPER_ADMIN') {
            return response()->json(['message' => 'Super Admin accounts cannot be deleted.'], 403);
        }
        if ($user->id === $request->user()->id) {
            return response()->json(['message' => 'You cannot delete your own account.'], 403);
        }
        $user->delete();
        return response()->json(['success' => true]);
    }

    // ---- Plans ----

    public function plans()
    {
        $plans = Plan::orderBy('sort')->orderBy('price')->get();
        return response()->json($plans->map(fn ($p) => [
            'id' => $p->id,
            'name' => $p->name,
            'code' => $p->code,
            'price' => $p->price,
            'gst_rate' => $p->gst_rate,
            'credits' => $p->credits,
            'features' => $p->features,
            'permissions' => $p->permissions,
            'is_active' => $p->is_active,
            'sort' => $p->sort,
        ]));
    }

    public function storePlan(Request $request)
    {
        $data = $this->planData($request);
        $plan = Plan::create($data);
        return response()->json($plan, 201);
    }

    public function updatePlan(Request $request, Plan $plan)
    {
        $data = $this->planData($request, $plan);
        $plan->update($data);
        return response()->json(['success' => true]);
    }

    public function destroyPlan(Plan $plan)
    {
        $plan->delete();
        return response()->json(['success' => true]);
    }

    // ---- Credit Packages ----

    public function creditPackages()
    {
        $packages = CreditPackage::orderBy('sort')->orderBy('credits')->get();
        return response()->json($packages);
    }

    public function storeCreditPackage(Request $request)
    {
        $data = $this->creditPackageData($request);
        $pkg = CreditPackage::create($data);
        return response()->json($pkg, 201);
    }

    public function updateCreditPackage(Request $request, CreditPackage $package)
    {
        $data = $this->creditPackageData($request);
        $package->update($data);
        return response()->json(['success' => true]);
    }

    public function toggleCreditPackage(CreditPackage $package)
    {
        $package->update(['is_active' => ! $package->is_active]);
        return response()->json(['success' => true, 'is_active' => $package->is_active]);
    }

    public function destroyCreditPackage(CreditPackage $package)
    {
        $package->delete();
        return response()->json(['success' => true]);
    }

    // ---- Topup ----

    public function topup(Request $request, CreditService $credits)
    {
        $data = $request->validate([
            'agency_id' => 'required|exists:agencies,id',
            'credits' => 'required|integer|min:1',
        ]);

        $newBalance = $credits->add(
            $data['agency_id'],
            $data['credits'],
            'admin_topup',
            "Admin topup by {$request->user()->name}",
            $request->user()->id
        );

        return response()->json(['success' => true, 'balance' => $newBalance]);
    }

    // ---- Private helpers ----

    private function assignPlan(User $user, ?int $planId): void
    {
        if (! $planId) return;
        $plan = Plan::find($planId);
        if (! $plan) return;

        $sub = Subscription::firstOrNew(['agency_id' => $user->agency_id]);
        $sub->plan = $plan->name;
        $sub->status = 'ACTIVE';
        $sub->monthly_credits = $plan->credits;
        if (! $sub->exists) {
            $sub->credit_balance = $plan->credits;
        }
        $sub->save();
    }

    private function planData(Request $request, ?Plan $plan = null): array
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'code' => ['required', 'string', 'max:40', Rule::unique('plans', 'code')->ignore($plan?->id)],
            'price' => 'required|integer|min:0',
            'gst_rate' => 'required|integer|min:0|max:50',
            'credits' => 'required|integer|min:0',
            'sort' => 'nullable|integer|min:0',
        ]);

        return [
            'name' => $request->string('name'),
            'code' => strtoupper(preg_replace('/[^A-Za-z0-9_]/', '', $request->input('code'))),
            'price' => (int) $request->input('price'),
            'gst_rate' => (int) $request->input('gst_rate'),
            'credits' => (int) $request->input('credits'),
            'features' => array_values(array_filter(array_map('trim', explode("\n", (string) $request->input('features', ''))))),
            'permissions' => array_values($request->input('permissions', [])),
            'is_active' => $request->boolean('is_active'),
            'sort' => (int) $request->input('sort', 0),
        ];
    }

    private function creditPackageData(Request $request): array
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'credits' => 'required|integer|min:1',
            'price' => 'required|integer|min:0',
            'gst_rate' => 'required|integer|min:0|max:50',
            'sort' => 'nullable|integer|min:0',
        ]);

        return [
            'name' => $request->string('name'),
            'credits' => (int) $request->input('credits'),
            'price' => (int) $request->input('price'),
            'gst_rate' => (int) $request->input('gst_rate'),
            'is_active' => $request->boolean('is_active'),
            'sort' => (int) $request->input('sort', 0),
        ];
    }
}
