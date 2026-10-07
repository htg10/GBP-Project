<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class MobileLoginController extends Controller
{
    public function consume(Request $request, string $code)
    {
        $userId = Cache::pull('mobile_login:' . $code);

        if (!$userId) {
            return redirect()->route('login')->with('error', 'Login link expire ho gaya. Dobara try karo.');
        }

        Auth::loginUsingId($userId);
        $request->session()->regenerate();

        $user = Auth::user();
        $sub = $user->agency?->subscription;
        $dest = (!$sub || $sub->status === 'TRIALING') ? route('plans') : route('dashboard');

        // Mobile app ka ek-time login link kabhi-kabhi ek specific page par continue
        // karna chahta hai (jaise Google connect). "next" sirf ek safe, local,
        // whitelisted path ho sakta hai — kisi aur domain par redirect nahi hota.
        $next = $request->query('next');
        if (is_string($next) && preg_match('#^/clients/\d+/(google|meta)/connect(\?.*)?$#', $next)) {
            return redirect($next);
        }

        return redirect()->intended($dest);
    }
}