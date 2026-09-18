<?php

namespace App\Http\Controllers;

use App\Models\Agency;
use App\Models\User;
use App\Models\Client;
use App\Models\Review;
use App\Models\Lead;
use App\Models\Subscription;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function overview(Request $request)
    {
        // Admin sees the WHOLE platform — every agency and every user.
        $stats = [
            'agencies' => Agency::count(),
            'users'    => User::count(),
            'clients'  => Client::count(),
            'reviews'  => Review::count(),
        ];

        // Per-agency review counts for the users table (one query, keyed by agency).
        $reviewsByAgency = Review::selectRaw('agency_id, COUNT(*) c')->groupBy('agency_id')->pluck('c', 'agency_id');

        $users = User::with('agency')->latest()->take(50)->get();

        $sub = Subscription::where('agency_id', $request->user()->agency_id)->first();

        return view('admin.overview', compact('stats', 'sub', 'users', 'reviewsByAgency'));
    }

    public function users(Request $request)
    {
        // Admin manages users across every agency.
        $users = User::with(['agency', 'client'])->latest()->get();
        // Clients the admin can bind users to (their own agency).
        $clients = Client::where('agency_id', $request->user()->agency_id)->orderBy('name')->get();
        return view('admin.users', compact('users', 'clients'));
    }

    public function storeUser(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'password' => 'required|string|min:8',
            'role' => 'required|in:SUPER_ADMIN,CLIENT_OWNER',
            'client_id' => 'nullable|exists:clients,id',
        ]);
        if (User::where('email', $data['email'])->exists()) {
            return back()->withErrors(['email' => 'Email already in use'])->withInput();
        }
        $data['agency_id'] = $request->user()->agency_id;
        $data['client_id'] = $data['client_id'] ?: null;

        // A Client must own exactly one business — auto-create it if none chosen.
        if ($data['role'] === 'CLIENT_OWNER' && ! $data['client_id']) {
            $client = Client::create(['agency_id' => $data['agency_id'], 'name' => $data['name'], 'email' => $data['email']]);
            $data['client_id'] = $client->id;
        }

        User::create($data);
        return back()->with('success', 'User created.');
    }

    public function updateUser(Request $request, User $user)
    {
        abort_unless($user->agency_id === $request->user()->agency_id, 403);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'role' => 'required|in:SUPER_ADMIN,CLIENT_OWNER',
            'password' => 'nullable|string|min:8',
            'client_id' => 'nullable|exists:clients,id',
        ]);
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->role = $data['role'];
        $clientId = $data['client_id'] ?: null;
        // Keep every Client Owner bound to a business.
        if ($data['role'] === 'CLIENT_OWNER' && ! $clientId) {
            $client = Client::create(['agency_id' => $user->agency_id, 'name' => $data['name'], 'email' => $data['email']]);
            $clientId = $client->id;
        }
        $user->client_id = $clientId;
        if (! empty($data['password'])) $user->password = $data['password'];
        $user->save();
        return back()->with('success', 'User updated.');
    }

    public function destroyUser(Request $request, User $user)
    {
        abort_unless($user->agency_id === $request->user()->agency_id, 403);
        if ($user->role === 'SUPER_ADMIN') {
            return back()->withErrors(['user' => 'Super Admin accounts cannot be deleted.']);
        }
        if ($user->id === $request->user()->id) {
            return back()->withErrors(['user' => 'You cannot delete your own account.']);
        }
        $user->delete();
        return back()->with('success', 'User deleted.');
    }
}
