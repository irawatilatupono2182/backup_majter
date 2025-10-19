<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AutoLoginAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Auto login admin user for every request
        if (!auth()->check()) {
            $user = \App\Models\User::where('email', 'admin@adamjaya.com')->first();
            if ($user) {
                // Mark email as verified
                if (!$user->email_verified_at) {
                    $user->email_verified_at = now();
                    $user->saveQuietly();
                }
                
                auth()->login($user);
            }
        }
        
        return $next($request);
    }
}
