<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Integration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Handles the Google OAuth 2.0 "connect account" flow for a client.
 *
 * Flow:
 *   1. /clients/{client}/google/connect  -> redirect user to Google consent screen
 *   2. Google redirects back to /google/callback?code=...&state=...
 *   3. We exchange the code for access + refresh tokens and store them
 *      in the integrations table (provider = GOOGLE_GBP).
 *
 * Requires GOOGLE_CLIENT_ID / GOOGLE_CLIENT_SECRET / GOOGLE_REDIRECT_URI in .env.
 * If those are blank, the "Connect" button stays informational and the app
 * keeps running on mock data.
 */
class GoogleOAuthController extends Controller
{
    private function configured(): bool
    {
        return config('services.google.client_id') && config('services.google.client_secret');
    }

    // Step 1 — send the user to Google.
    public function connect(Request $request, Client $client)
    {
        abort_unless($client->agency_id === $request->user()->agency_id, 403);

        // Mobile app ke flow mein error ke baad wapas "back()" se bhejna theek nahi
        // (mobile browser session mein koi meaningful "previous page" nahi hota),
        // isliye mobile requests ke liye hamesha app ke custom scheme par redirect karte hain.
        $isMobile = $request->boolean('mobile');
        $mobileError = fn (string $message) => redirect('eydia://google-connected?status=error&message=' . urlencode($message));

        if (! $request->user()->email_verified_at) {
            $message = 'Please verify your email address before connecting Google Business Profile.';
            return $isMobile ? $mobileError($message) : back()->with('error', $message);
        }

        if (! $this->configured()) {
            $message = 'Google is not configured yet. Add GOOGLE_CLIENT_ID and GOOGLE_CLIENT_SECRET to your .env first.';
            return $isMobile ? $mobileError($message) : back()->with('error', $message);
        }

        // "state" protects against CSRF and remembers which client we're connecting.
        // We store it in the CACHE (not the session) keyed by the state value, because
        // the session cookie can get lost across the Google redirect. The state itself
        // is the random secret Google echoes back, so it's safe to use as the key.
        $state = Str::random(40);
        Cache::put('gauth_'.$state, [
            'client_id' => $client->id,
            'agency_id' => $request->user()->agency_id,
            'mobile' => $isMobile,
        ], now()->addMinutes(15));

        $params = http_build_query([
            'client_id' => config('services.google.client_id'),
            'redirect_uri' => config('services.google.redirect'),
            'response_type' => 'code',
            'scope' => implode(' ', config('services.google.scopes')),
            'access_type' => 'offline',   // so we get a refresh_token
            'prompt' => 'consent',        // force refresh_token every time
            'state' => $state,
        ]);

        return redirect('https://accounts.google.com/o/oauth2/v2/auth?'.$params);
    }

    // Step 2 — Google redirects back here with a code.
    public function callback(Request $request)
    {
        // Pulled once up front so every exit path (including "user cancelled")
        // knows whether this was the app's flow and can send the browser back
        // to the app instead of the web dashboard.
        $state = $request->query('state');
        $stored = $state ? Cache::pull('gauth_'.$state) : null;
        $isMobile = $stored['mobile'] ?? false;
        $mobileError = fn (string $message) => redirect('eydia://google-connected?status=error&message=' . urlencode($message));

        if ($request->filled('error')) {
            $message = 'Google connection was cancelled.';
            return $isMobile ? $mobileError($message) : redirect()->route('clients')->with('error', $message);
        }

        if (! $stored) {
            $message = 'Invalid state. Please try connecting again.';
            return $isMobile ? $mobileError($message) : redirect()->route('clients')->with('error', $message);
        }

        $client = Client::find($stored['client_id']);
        if (! $client || $client->agency_id !== $stored['agency_id']) {
            $message = 'Client not found.';
            return $isMobile ? $mobileError($message) : redirect()->route('clients')->with('error', $message);
        }

        // Exchange the authorization code for tokens.
        $res = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'code' => $request->query('code'),
            'client_id' => config('services.google.client_id'),
            'client_secret' => config('services.google.client_secret'),
            'redirect_uri' => config('services.google.redirect'),
            'grant_type' => 'authorization_code',
        ]);

        if (! $res->successful()) {
            $message = 'Could not get token from Google: '.$res->body();
            return $isMobile
                ? $mobileError('Could not get token from Google.')
                : redirect()->route('clients.show', $client)->with('error', $message);
        }

        $tokens = $res->json();

        Integration::updateOrCreate(
            ['client_id' => $client->id, 'provider' => 'GOOGLE_GBP'],
            [
                'agency_id' => $client->agency_id,
                'access_token' => $tokens['access_token'] ?? null,
                'refresh_token' => $tokens['refresh_token'] ?? null,
                'expires_at' => isset($tokens['expires_in']) ? now()->addSeconds($tokens['expires_in']) : null,
                'meta' => ['scope' => $tokens['scope'] ?? null],
            ]
        );

        if ($isMobile) {
            return redirect('eydia://google-connected?status=success&client=' . $client->id);
        }

        return redirect()->route('clients.show', $client)
            ->with('success', 'Google connected! You can now sync reviews for this client.');
    }

    // Disconnect — remove stored tokens.
    public function disconnect(Request $request, Client $client)
    {
        abort_unless($client->agency_id === $request->user()->agency_id, 403);
        Integration::where('client_id', $client->id)->where('provider', 'GOOGLE_GBP')->delete();

        return back()->with('success', 'Google disconnected.');
    }
}
