<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DisableEmailVerification
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Mark all authenticated users as verified
        if (auth()->check() && auth()->user()) {
            $user = auth()->user();
            if (!$user->email_verified_at) {
                $user->email_verified_at = now();
                $user->saveQuietly(); // Save without triggering events
            }
        }
        
        return $next($request);
    }
}
