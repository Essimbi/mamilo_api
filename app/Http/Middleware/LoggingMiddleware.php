<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Logging Middleware
 * 
 * Log toutes les requêtes API avec:
 * - Méthode HTTP
 * - URL
 * - IP utilisateur
 * - Utilisateur authentifié
 * - Status de réponse
 * - Temps d'exécution
 */
class LoggingMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Log la requête entrante
        Log::info('Incoming Request', [
            'method' => $request->getMethod(),
            'url' => $request->getPathInfo(),
            'ip' => $request->ip(),
            'user_id' => auth()->id(),
            'user_agent' => $request->userAgent(),
        ]);

        // Mesure le temps d'exécution
        $startTime = microtime(true);

        $response = $next($request);

        $duration = round((microtime(true) - $startTime) * 1000); // en ms

        // Log la réponse
        Log::info('Outgoing Response', [
            'method' => $request->getMethod(),
            'url' => $request->getPathInfo(),
            'status' => $response->status(),
            'duration_ms' => $duration,
            'ip' => $request->ip(),
            'user_id' => auth()->id(),
        ]);

        // Log les erreurs
        if ($response->status() >= 400) {
            Log::warning('Request Failed', [
                'method' => $request->getMethod(),
                'url' => $request->getPathInfo(),
                'status' => $response->status(),
                'ip' => $request->ip(),
            ]);
        }

        return $response;
    }
}
