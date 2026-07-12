<?php

namespace App\Providers;

use App\Events\SparepartUpdated;
use App\Listeners\SyncToDriveListener;
use App\Models\Spareparts;
use App\Observers\SparepartObserver;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use App\Models\User;

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

Route::bind('user', function ($value) {
        $id = app(\App\Services\HashIdService::class)->decode($value);
        abort_if(!$id, 404);
        return User::findOrFail($id);
    });
    }

    /**
     * Daftar event dan listener (LARAVEL 12: BOLEH DI AppServiceProvider!)
     */
    protected $listen = [
        SparepartUpdated::class => [
            SyncToDriveListener::class,
        ],
    ];
}
