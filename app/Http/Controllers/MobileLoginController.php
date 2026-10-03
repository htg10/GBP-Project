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

        return redirect()->route('dashboard');
    }
}