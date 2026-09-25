<?php

namespace App\Http\Controllers;

use App\Models\Agency;
use App\Models\User;
use App\Models\Client;
use App\Models\Review;
use App\Models\Lead;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function overview(Request $request)
    {
        $stats = [
            'agencies' => Agency::count(),
            'users'    => User::count(),
            'clients'  => Client::count(),
            'reviews'  => Review::count(),
        ];

        $reviewsByAgency = Review::selectRaw('agency_id, COUNT(*) c')->groupBy('agency_id')->pluck('c', 'agency_id');

        $users = User::with('agency')->latest()->take(50)->get();

        $sub = Subscription::where('agency_id', $request->user()->agency_id)->first();

        return view('admin.overview', compact('stats', 'sub', 'users', 'reviewsByAgency'));
    }

    public function users(Request $request)
    {
        $users = User::with(['agency', 'client'])->latest()->get();
        $plans = Plan::where('is_active', true)->orderBy('sort')->orderBy('price')->get();
        $subscriptions = Subscription::all()->keyBy('agency_id');
        return view('admin.users', compact('users', 'plans', 'subscriptions'));
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
            return back()->withErrors(['email' => 'Email already in use'])->withInput();
        }
        $data['agency_id'] = $request->user()->agency_id;

        if ($data['role'] === 'CLIENT_OWNER') {
            $client = Client::create(['agency_id' => $data['agency_id'], 'name' => $data['name'], 'email' => $data['email']]);
            $data['client_id'] = $client->id;
        }

        $user = User::create($data);

        if (!empty($data['plan_id'])) {
            $this->assignPlan($user, $data['plan_id']);
        }

        return back()->with('success', 'User created.');
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
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->role = $data['role'];

        if ($data['role'] === 'CLIENT_OWNER' && !$user->client_id) {
            $client = Client::create(['agency_id' => $user->agency_id, 'name' => $data['name'], 'email' => $data['email']]);
            $user->client_id = $client->id;
        }

        if (!empty($data['password'])) $user->password = $data['password'];
        $user->save();

        if (array_key_exists('plan_id', $data)) {
            $this->assignPlan($user, $data['plan_id']);
        }

        return back()->with('success', 'User updated.');
    }

    public function destroyUser(Request $request, User $user)
    {
        if ($user->role === 'SUPER_ADMIN') {
            return back()->withErrors(['user' => 'Super Admin accounts cannot be deleted.']);
        }
        if ($user->id === $request->user()->id) {
            return back()->withErrors(['user' => 'You cannot delete your own account.']);
        }
        $user->delete();
        return back()->with('success', 'User deleted.');
    }

    private function assignPlan(User $user, ?int $planId): void
    {
        if (!$planId) return;
        $plan = Plan::find($planId);
        if (!$plan) return;

        $sub = Subscription::firstOrNew(['agency_id' => $user->agency_id]);
        $sub->plan = $plan->name;
        $sub->status = 'ACTIVE';
        $sub->monthly_credits = $plan->credits;
        if (!$sub->exists) {
            $sub->credit_balance = $plan->credits;
        }
        $sub->save();
    }
}
