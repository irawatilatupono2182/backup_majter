<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class DebugFilamentAuth
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        Log::info('=== MIDDLEWARE DEBUG ===', [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'auth_check' => auth()->check(),
            'auth_guard' => config('filament.auth.guard', 'web'),
            'auth_id' => auth()->id(),
            'user' => auth()->user() ? [
                'id' => auth()->user()->id,
                'email' => auth()->user()->email,
                'is_active' => auth()->user()->is_active,
            ] : null,
            'session_id' => session()->getId(),
            'session_has_data' => !empty(session()->all()),
            'session_keys' => array_keys(session()->all()),
            'has_laravel_session_cookie' => $request->hasCookie(config('session.cookie')),
            'browser_session_cookie' => $request->cookie(config('session.cookie')),
            'all_cookies' => array_keys($request->cookies->all()),
        ]);

        $response = $next($request);

        $setCookieHeaders = $response->headers->getCookies();
        $sessionCookieInfo = null;
        foreach ($setCookieHeaders as $cookie) {
            if ($cookie->getName() === config('session.cookie')) {
                $sessionCookieInfo = [
                    'name' => $cookie->getName(),
                    'value' => $cookie->getValue(),
                    'domain' => $cookie->getDomain(),
                    'path' => $cookie->getPath(),
                ];
            }
        }

        Log::info('=== AFTER MIDDLEWARE ===', [
            'status' => $response->getStatusCode(),
            'redirect_to' => $response->headers->get('Location'),
            'auth_check_after' => auth()->check(),
            'session_id_after' => session()->getId(),
            'set_cookie_session' => $sessionCookieInfo,
            'has_set_cookie_header' => !empty($setCookieHeaders),
        ]);

        return $response;
    }
}
