<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\DeliveryNote;
use App\Observers\DeliveryNoteObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register custom LoginResponse - DISABLED for now
        // $this->app->singleton(
        //     \Filament\Http\Responses\Auth\Contracts\LoginResponse::class,
        //     \App\Http\Responses\LoginResponse::class
        // );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register DeliveryNote Observer for automatic stock movement integration
        DeliveryNote::observe(DeliveryNoteObserver::class);
    }
}

