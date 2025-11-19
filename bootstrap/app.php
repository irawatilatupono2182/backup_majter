<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Use custom EncryptCookies middleware that excludes session cookies
        $middleware->encryptCookies(except: [
            'laravel_session',
            'si-majter-session',
            'XSRF-TOKEN',
        ]);
        
        // Use custom VerifyCsrfToken middleware
        $middleware->validateCsrfTokens(except: [
            'admin/login',
            'livewire/*',
            'livewire/update',
            'livewire/message/*',
        ]);

        // Performance optimization middleware
        $middleware->append(\App\Http\Middleware\OptimizeMiddleware::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
