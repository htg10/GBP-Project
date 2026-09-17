<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\AiService;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\GoogleProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(AiService::class, fn () => new AiService());
    }

    public function boot(): void
    {
        // "Sign in with Google" uses a SEPARATE Google OAuth config from the
        // Business-Profile connection (different scopes + redirect URI), so we
        // register a custom 'google_login' Socialite driver backed by the
        // services.google_login config.
        Socialite::extend('google_login', function ($app) {
            $config = $app['config']['services.google_login'];
            return Socialite::buildProvider(GoogleProvider::class, $config);
        });
    }
}
