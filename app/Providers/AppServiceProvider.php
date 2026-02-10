<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\DeliveryNote;
use App\Models\PurchaseOrder;
use App\Models\PurchasePayment;
use App\Observers\DeliveryNoteObserver;
use App\Observers\PurchaseOrderObserver;
use App\Observers\PurchasePaymentObserver;

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
        // Temporarily disabled to debug bootstrap issue
        // DeliveryNote::observe(DeliveryNoteObserver::class);
        
        // Register PurchaseOrder Observer for automatic payable creation
        // PurchaseOrder::observe(PurchaseOrderObserver::class);
        
        // Register PurchasePayment Observer for automatic payable update
        // PurchasePayment::observe(PurchasePaymentObserver::class);

        // Performance Optimizations
        if ($this->app->environment('production')) {
            // URL generation optimization
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        // Model optimization - prevent lazy loading only in strict development mode
        // Disabled because it's too strict for Filament
        // if ($this->app->environment('local')) {
        //     \Illuminate\Database\Eloquent\Model::preventLazyLoading();
        // }
    }
}

