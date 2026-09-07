<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Intentionally empty: keep the API foundation minimal.
    }

    public function boot(): void
    {
        // Intentionally empty.
    }
}
