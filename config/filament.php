<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Filament Performance Optimizations
    |--------------------------------------------------------------------------
    */

    'cache' => [
        'navigation' => [
            'enabled' => env('FILAMENT_CACHE_NAVIGATION', true),
            'ttl' => env('FILAMENT_CACHE_NAVIGATION_TTL', 3600), // 1 hour
        ],
        'widgets' => [
            'enabled' => env('FILAMENT_CACHE_WIDGETS', true),
            'ttl' => env('FILAMENT_CACHE_WIDGETS_TTL', 300), // 5 minutes
        ],
    ],

    'lazy_loading' => [
        'tables' => env('FILAMENT_LAZY_LOAD_TABLES', true),
        'forms' => env('FILAMENT_LAZY_LOAD_FORMS', true),
    ],

    'pagination' => [
        'default_per_page' => env('FILAMENT_PAGINATION_PER_PAGE', 25),
        'options' => [10, 25, 50, 100],
    ],

];
