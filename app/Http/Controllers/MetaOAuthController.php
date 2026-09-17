<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Integration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Handles the Meta (Facebook Login for Business) OAuth flow for a client.
 *
 * Flow:
 *   1. /clients/{client}/meta/connect -> redirect to Facebook's consent screen
 *   2. Facebook redirects back to /meta/callback?code=...&state=...
 *   3. We exchange the code for a short-lived user token, upgrade it to a
 *      long-lived one, then call /me/accounts to find the Pages this user
 *      manages. We store the FIRST page's page-scoped access token (which
 *      never expires as long as the long-lived user token stays valid) plus
 *      its linked Instagram Business Account id, under provider = META_GRAPH.
 *
 * Requires META_APP_ID / META_APP_SECRET / META_REDIRECT_URI in .env. If
 * those are blank, the "Connect" button stays informational and social posts
 * are just saved locally (no auto-publish).
 */
class MetaOAuthController extends Controller
{
    private function configured(): bool
    {
        return config('services.meta.app_id') && config('services.meta.app_secret');
    }

    private function graph(): string
    {
        return 'https://graph.facebook.com/'.config('services.meta.graph_version');
    }

    // Step 1 — send the user to Facebook.
    public function connect(Request $request, Client $client)
    {
        abort_unless($client->agency_id === $request->user()->agency_id, 403);

        if (! $this->configured()) {
            return back()->with('error', 'Meta is not configured yet. Add META_APP_ID and META_APP_SECRET to your .env first.');
        }

        // Cached (not session) — same reasoning as the Google OAuth flow: the
        // session cookie can drop across the redirect through facebook.com.
        $state = Str::random(40);
        Cache::put('mauth_'.$state, [
            'client_id' => $client->id,
            'agency_id' => $request->user()->agency_id,
        ], now()->addMinutes(15));

        $params = http_build_query([
            'client_id' => config('services.meta.app_id'),
            'redirect_uri' => config('services.meta.redirect'),
            'response_type' => 'code',
            'scope' => implode(',', config('services.meta.scopes')),
            'state' => $state,
        ]);

        return redirect('https://www.facebook.com/'.config('services.meta.graph_version').'/dialog/oauth?'.$params);
    }

    // Step 2 — Facebook redirects back here with a code.
    public function callback(Request $request)
    {
        if ($request->filled('error')) {
            return redirect()->route('clients')->with('error', 'Meta connection was cancelled.');
        }

        $state = $request->query('state');
        $stored = $state ? Cache::pull('mauth_'.$state) : null;
        if (! $stored) {
            return redirect()->route('clients')->with('error', 'Invalid state. Please try connecting again.');
        }

        $client = Client::find($stored['client_id']);
        if (! $client || $client->agency_id !== $stored['agency_id']) {
            return redirect()->route('clients')->with('error', 'Client not found.');
        }

        // Exchange code -> short-lived user token.
        $res = Http::get($this->graph().'/oauth/access_token', [
            'client_id' => config('services.meta.app_id'),
            'client_secret' => config('services.meta.app_secret'),
            'redirect_uri' => config('services.meta.redirect'),
            'code' => $request->query('code'),
        ]);

        if (! $res->successful()) {
            return redirect()->route('clients.show', $client)
                ->with('error', 'Could not get token from Meta: '.$res->body());
        }

        $shortToken = $res->json('access_token');

        // Upgrade to a long-lived user token (~60 days).
        $long = Http::get($this->graph().'/oauth/access_token', [
            'grant_type' => 'fb_exchange_token',
            'client_id' => config('services.meta.app_id'),
            'client_secret' => config('services.meta.app_secret'),
            'fb_exchange_token' => $shortToken,
        ]);
        $userToken = $long->successful() ? $long->json('access_token') : $shortToken;
        $expiresIn = $long->successful() ? $long->json('expires_in') : null;

        // Find the Pages this user manages.
        $pages = Http::get($this->graph().'/me/accounts', [
            'access_token' => $userToken,
            'fields' => 'id,name,access_token,instagram_business_account',
        ]);

        if (! $pages->successful() || empty($pages->json('data'))) {
            return redirect()->route('clients.show', $client)
                ->with('error', 'Connected to Meta, but no Facebook Page was found. Make sure this user is an admin of a Page.');
        }

        $page = $pages->json('data')[0]; // MVP: first Page returned.

        Integration::updateOrCreate(
            ['client_id' => $client->id, 'provider' => 'META_GRAPH'],
            [
                'agency_id' => $client->agency_id,
                'access_token' => $page['access_token'], // page-scoped token — used for publishing
                'refresh_token' => $userToken,            // long-lived user token, kept for re-deriving page tokens
                'expires_at' => $expiresIn ? now()->addSeconds($expiresIn) : null,
                'meta' => [
                    'page_id' => $page['id'],
                    'page_name' => $page['name'],
                    'ig_user_id' => $page['instagram_business_account']['id'] ?? null,
                ],
            ]
        );

        $igNote = isset($page['instagram_business_account']) ? ' + Instagram' : ' (no linked Instagram account found)';
        return redirect()->route('clients.show', $client)
            ->with('success', "Meta connected — Page \"{$page['name']}\"{$igNote}. Social posts now publish for real.");
    }

    // Disconnect — remove stored tokens.
    public function disconnect(Request $request, Client $client)
    {
        abort_unless($client->agency_id === $request->user()->agency_id, 403);
        Integration::where('client_id', $client->id)->where('provider', 'META_GRAPH')->delete();

        return back()->with('success', 'Meta disconnected.');
    }
}
