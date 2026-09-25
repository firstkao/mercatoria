<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Contracts\View\View as ViewContract;
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
        View::composer('layouts.app', function (ViewContract $view): void {
            $view->with([
                'promoBarText' => Setting::get('promo_bar_text'),
                'hasLogo' => file_exists(public_path('images/logo.png')),
            ]);
        });
    }
}