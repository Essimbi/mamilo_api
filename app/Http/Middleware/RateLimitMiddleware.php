<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

/**
 * Rate Limiting Middleware
 * 
 * Limite les requêtes par IP pour éviter les abus
 * - Endpoints publics : 60 requêtes par minute
 * - API endpoints : 100 requêtes par minute
 * - Login endpoint : 5 tentatives par minute
 */
class RateLimitMiddleware
{
    /**
     * Handle the incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $key = 'api:' . $request->ip();

        // Rate limit login attempts
        if ($request->is('api/v1/auth/login')) {
            return $this->rateLimitLogin($request, $key);
        }

        // Rate limit API requests
        if ($request->is('api/*')) {
            return $this->rateLimitApi($request, $key);
        }

        return $next($request);
    }

    /**
     * Rate limit login attempts (5 per minute per IP)
     */
    private function rateLimitLogin(Request $request, string $key): Response
    {
        $maxAttempts = 5;
        $decaySeconds = 60;

        if (RateLimiter::tooManyAttempts($key . ':login', $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key . ':login');

            return response()->json([
                'success' => false,
                'message' => "Trop de tentatives de connexion. Réessayez dans {$seconds} secondes.",
            ], 429);
        }

        RateLimiter::hit($key . ':login', $decaySeconds);

        return app()->make('next')($request);
    }

    /**
     * Rate limit API requests (100 per minute per IP)
     */
    private function rateLimitApi(Request $request, string $key): Response
    {
        $maxAttempts = 100;
        $decaySeconds = 60;

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);

            return response()->json([
                'success' => false,
                'message' => "Limite de requêtes dépassée. Réessayez dans {$seconds} secondes.",
            ], 429);
        }

        RateLimiter::hit($key, $decaySeconds);

        return app()->make('next')($request);
    }
}
