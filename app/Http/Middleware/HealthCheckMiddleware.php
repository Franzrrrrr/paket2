<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HealthCheckMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip middleware for health checks
        if ($request->is('up') || $request->is('health') || $request->is('ping')) {
            return $next($request);
        }

        return $next($request);
    }
}
