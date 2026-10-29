<?php

namespace App\Providers;

use App\Models\Developer;
use App\Models\Game;
use App\Models\UserNotification;
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

        // Navbar dropdown Game / Developer → route katalog (PRD 7.2) + bell (PRD 43.2).
        View::composer(['layouts.app', 'frontend.profile.show'], function ($view): void {
            $user = auth()->user();

            $view->with(
                'navGames',
                Game::where('is_active', true)->orderBy('name')->get(['id', 'name', 'slug'])
            )->with(
                'navDevelopers',
                Developer::where('is_active', true)->orderBy('name')->get(['id', 'name', 'slug'])
            )->with(
                'bellNotifications',
                $user
                    ? UserNotification::where('user_id', $user->id)->orderByDesc('id')->limit(10)->get()
                    : collect()
            )->with(
                'bellUnreadCount',
                $user
                    ? (int) UserNotification::where('user_id', $user->id)->where('is_read', false)->count()
                    : 0
            );
        });
    }
}
