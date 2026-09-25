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
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'error' => 'This feature requires an active plan.',
                    'upgrade_url' => route('plans'),
                    'needs_plan' => true,
                ], 402);
            }

            return redirect()->route('plans')->with('error', 'This AI feature requires an active plan. Choose a plan to unlock AI replies, audits, and more.');
        }

        return $next($request);
    }
}
