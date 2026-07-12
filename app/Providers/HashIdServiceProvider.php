<?php

namespace App\Providers;

use App\Services\HashIdService;
use Illuminate\Support\ServiceProvider;

class HashIdServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register()
    {
        $this->app->singleton(HashIdService::class, function () {
            return new HashIdService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
