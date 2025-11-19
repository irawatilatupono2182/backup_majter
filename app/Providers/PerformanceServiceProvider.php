<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Blade;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PerformanceServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Defer loading of service providers that are not needed immediately
        if ($this->app->environment('production')) {
            // Optimize collections
            $this->app->singleton('collection.factory', function ($app) {
                return new \Illuminate\Support\Collection();
            });
        }
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Optimize Eloquent
        Model::shouldBeStrict(! $this->app->isProduction());
        
        // Cache Blade templates aggressively
        if ($this->app->environment('production')) {
            Blade::setCompiledPath(storage_path('framework/views'));
        }

        // Optimize database queries
        if ($this->app->environment('local')) {
            // Log slow queries in development
            DB::listen(function ($query) {
                if ($query->time > 1000) { // Queries slower than 1 second
                    logger()->warning('Slow query detected', [
                        'sql' => $query->sql,
                        'bindings' => $query->bindings,
                        'time' => $query->time . 'ms'
                    ]);
                }
            });
        }

        // Optimize View loading
        View::share('app_name', config('app.name'));
        View::share('app_version', '1.0.0');

        // Preload commonly used configurations
        $this->preloadConfigurations();
    }

    /**
     * Preload frequently accessed configuration values
     */
    private function preloadConfigurations(): void
    {
        // Cache these configs in memory
        config([
            'app.name' => config('app.name'),
            'app.env' => config('app.env'),
            'app.debug' => config('app.debug'),
        ]);
    }
}
