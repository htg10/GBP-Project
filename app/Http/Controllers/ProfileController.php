<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function edit(Request $request)
    {
        return view('dashboard.profile', ['user' => $request->user()]);
    }

    public function update(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string',
            'avatar' => 'nullable|string', // base64 data URL
            'current_password' => 'nullable|string',
            'new_password' => 'nullable|string|min:8',
        ]);

        $user->name = $data['name'];
        $user->phone = $data['phone'] ?? null;
        if (! empty($data['avatar'])) {
            $user->avatar = $data['avatar'];
        }

        if (! empty($data['new_password'])) {
            if (! Hash::check($data['current_password'] ?? '', $user->password)) {
                return back()->withErrors(['current_password' => 'Current password is incorrect']);
            }
            $user->password = $data['new_password'];
        }

        $user->save();
        return back()->with('success', 'Profile updated.');
    }
}
