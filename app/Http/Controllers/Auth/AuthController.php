<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Agency;
use App\Models\User;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'agency_name' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'password' => 'required|string|min:8',
        ]);

        if (User::where('email', $data['email'])->exists()) {
            return back()->withErrors(['email' => 'Email already in use'])->withInput();
        }

        $agency = Agency::create([
            'name' => $data['agency_name'],
            'slug' => Str::slug($data['agency_name']).'-'.Str::lower(Str::random(5)),
        ]);

        // First user of an agency becomes its owner.
        $user = User::create([
            'agency_id' => $agency->id,
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => 'SUPER_ADMIN',
        ]);

        Subscription::create(['agency_id' => $agency->id, 'plan' => 'STARTER', 'status' => 'TRIALING']);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (! Auth::attempt($data, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Invalid credentials'])->withInput();
        }

        $request->session()->regenerate();

        // Admins go to admin panel, others to dashboard.
        return redirect()->intended(Auth::user()->isAdmin() ? route('admin.overview') : route('dashboard'));
    }

    public function redirectToGoogle()
    {
        return Socialite::driver('google_login')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google_login')->user();
        } catch (\Exception $e) {
            return redirect()->route('login')->withErrors(['email' => 'Google login failed. Please try again.']);
        }

        $user = User::where('email', $googleUser->getEmail())->first();

        if ($user) {
            if (! $user->avatar && $googleUser->getAvatar()) {
                $user->update(['avatar' => $googleUser->getAvatar()]);
            }
            Auth::login($user, true);
            return redirect()->intended(
                $user->isAdmin() ? route('admin.overview') : route('dashboard')
            );
        }

        $agency = Agency::create([
            'name' => $googleUser->getName() . "'s Agency",
            'slug' => Str::slug($googleUser->getName()) . '-' . Str::lower(Str::random(5)),
        ]);

        $user = User::create([
            'agency_id' => $agency->id,
            'name' => $googleUser->getName(),
            'email' => $googleUser->getEmail(),
            'password' => Hash::make(Str::random(24)),
            'role' => 'SUPER_ADMIN',
            'avatar' => $googleUser->getAvatar(),
        ]);

        Subscription::create(['agency_id' => $agency->id, 'plan' => 'STARTER', 'status' => 'TRIALING']);

        Auth::login($user, true);
        return redirect()->route('dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
