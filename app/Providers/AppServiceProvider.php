<?php

namespace App\Providers;

use App\Models\Developer;
use App\Models\Game;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('login', function (Request $request): Limit {
            return Limit::perMinute((int) setting('login_rate_limit_per_minute', 5))->by($request->ip());
        });

        RateLimiter::for('register', function (Request $request): Limit {
            return Limit::perMinute(3)->by($request->ip());
        });

        // Navbar dropdown Game / Developer → route katalog (PRD 7.2).
        View::composer('layouts.app', function ($view): void {
            $view->with(
                'navGames',
                Game::where('is_active', true)->orderBy('name')->get(['id', 'name', 'slug'])
            )->with(
                'navDevelopers',
                Developer::where('is_active', true)->orderBy('name')->get(['id', 'name', 'slug'])
            );
        });
    }
}
