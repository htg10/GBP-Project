<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function index(Request $request)
    {
        $this->guard($request);

        $team = User::where('client_id', $request->user()->client_id)
            ->where('id', '!=', $request->user()->id)
            ->latest()
            ->get();

        return response()->json($team->map(fn ($u) => [
            'id' => $u->id,
            'name' => $u->name,
            'email' => $u->email,
            'role' => $u->role,
            'created_at' => $u->created_at,
        ]));
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
            return response()->json(['message' => 'Email already in use.'], 422);
        }

        $user = User::create([
            'agency_id' => $request->user()->agency_id,
            'client_id' => $request->user()->client_id,
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => $data['role'],
        ]);

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
        ], 201);
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
            return response()->json(['message' => 'Email already in use.'], 422);
        }

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->role = $data['role'];
        if (! empty($data['password'])) {
            $user->password = $data['password'];
        }
        $user->save();

        return response()->json(['success' => true]);
    }

    public function destroy(Request $request, User $user)
    {
        $this->guard($request);
        abort_unless($user->client_id === $request->user()->client_id, 403);
        abort_if($user->id === $request->user()->id, 403);

        $user->delete();

        return response()->json(['success' => true]);
    }

    private function guard(Request $request): void
    {
        abort_unless($request->user()->role === 'CLIENT_OWNER' && $request->user()->client_id, 403);
    }
}
