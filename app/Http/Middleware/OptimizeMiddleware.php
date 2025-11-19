<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OptimizeMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Enable output compression
        if (!$request->headers->has('Accept-Encoding') || 
            !str_contains($request->headers->get('Accept-Encoding'), 'gzip')) {
            // Client doesn't support gzip
        } else {
            if (!ob_start('ob_gzhandler')) {
                ob_start();
            }
        }

        // Add performance hints
        $response = $next($request);

        // Add cache headers for static assets
        if ($request->is('css/*') || $request->is('js/*') || $request->is('images/*')) {
            $response->header('Cache-Control', 'public, max-age=31536000, immutable');
        }

        // Add preconnect hints for external resources
        $response->header('Link', '</css/app.css>; rel=preload; as=style', false);
        $response->header('Link', '</js/app.js>; rel=preload; as=script', false);

        // Remove unnecessary headers
        $response->headers->remove('X-Powered-By');

        return $response;
    }
}
