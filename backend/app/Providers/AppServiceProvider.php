<?php

namespace App\Providers;

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
        // Маршруты Sanctum (/sanctum/csrf-cookie) регистрируются автоматически
        // через Service Provider, если не вызван Sanctum::ignoreRoutes()
    }
}
