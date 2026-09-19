<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureActivePlan
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->role === 'SUPER_ADMIN') {
            return $next($request);
        }

        $sub = $user->agency?->subscription;

        if (! $sub || $sub->status === 'TRIALING') {
            return redirect()->route('plans')->with('error', 'Please purchase a plan to access this feature.');
        }

        return $next($request);
    }
}
