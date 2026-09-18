<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

/**
 * Client-side team management. A Client Owner adds Staff / Marketing Managers
 * that live STRICTLY under their own client — never visible to other clients.
 */
class TeamController extends Controller
{
    private function guard(Request $request): void
    {
        abort_unless(
            $request->user()->role === 'CLIENT_OWNER' && $request->user()->client_id,
            403,
            'Only a Client Owner can manage their team.'
        );
    }

    public function index(Request $request)
    {
        $this->guard($request);
        $clientId = $request->user()->client_id;

        // Only this client's own members (excluding the owner themselves).
        $team = User::where('client_id', $clientId)
            ->where('id', '!=', $request->user()->id)
            ->latest()
            ->get();

        return view('dashboard.team', compact('team'));
    }

    public function store(Request $request)
    {
        $this->guard($request);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'password' => 'required|string|min:8',
            'role' => 'required|in:STAFF,MARKETING_MANAGER',
        ]);

        if (User::where('email', $data['email'])->exists()) {
            return back()->withErrors(['email' => 'Email already in use'])->withInput();
        }

        User::create([
            'agency_id' => $request->user()->agency_id,
            'client_id' => $request->user()->client_id, // bound to this client
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => $data['role'],
        ]);

        return back()->with('success', 'Team member added under your business.');
    }

    public function update(Request $request, User $user)
    {
        $this->guard($request);
        abort_unless($user->client_id === $request->user()->client_id, 403);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'role' => 'required|in:STAFF,MARKETING_MANAGER',
            'password' => 'nullable|string|min:8',
        ]);

        if (User::where('email', $data['email'])->where('id', '!=', $user->id)->exists()) {
            return back()->withErrors(['email' => 'Email already in use']);
        }

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->role = $data['role'];
        if (! empty($data['password'])) $user->password = $data['password'];
        $user->save();

        return back()->with('success', 'Team member updated.');
    }

    public function destroy(Request $request, User $user)
    {
        $this->guard($request);
        // Can only remove members of their own client.
        abort_unless($user->client_id === $request->user()->client_id, 403);
        abort_if($user->id === $request->user()->id, 403);
        $user->delete();

        return back()->with('success', 'Team member removed.');
    }
}
