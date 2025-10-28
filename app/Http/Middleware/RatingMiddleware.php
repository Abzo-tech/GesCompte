<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Exceptions\RateLimitExceededException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware pour limiter le taux d'interrogation des API
 *
 * Enregistre les utilisateurs qui ont atteint la limite de taux
 * Bloque temporairement les requêtes excessives
 */
class RatingMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, int $maxRequests = 60, int $windowMinutes = 1): Response
    {
        // Clé de cache unique par utilisateur et par endpoint
        $key = $this->resolveRequestSignature($request);
        $cacheKey = "rate_limit:{$key}";

        // Récupérer le nombre de requêtes actuelles
        $requests = Cache::get($cacheKey, 0);

        // Vérifier si la limite est atteinte
        if ($requests >= $maxRequests) {
            // Enregistrer l'utilisateur qui a atteint la limite
            $this->logRateLimitExceeded($request, $key);

            throw new RateLimitExceededException(
                "Trop de requêtes. Limite : {$maxRequests} requêtes par {$windowMinutes} minute(s)."
            );
        }

        // Incrémenter le compteur
        Cache::put($cacheKey, $requests + 1, now()->addMinutes($windowMinutes));

        // Ajouter les headers de rate limiting
        $response = $next($request);
        $response->headers->set('X-RateLimit-Limit', $maxRequests);
        $response->headers->set('X-RateLimit-Remaining', max(0, $maxRequests - $requests - 1));
        $response->headers->set('X-RateLimit-Reset', now()->addMinutes($windowMinutes)->timestamp);

        return $response;
    }

    /**
     * Résoudre la signature unique de la requête
     */
    protected function resolveRequestSignature(Request $request): string
    {
        // Utiliser l'IP si pas d'utilisateur authentifié
        $userId = $request->user()?->id ?? $request->ip();

        return hash('sha256', $userId . '|' . $request->path());
    }

    /**
     * Enregistrer les utilisateurs qui ont atteint la limite
     */
    protected function logRateLimitExceeded(Request $request, string $key): void
    {
        $logKey = "rate_limit_exceeded:{$key}";

        // Enregistrer pendant 24 heures
        Cache::put($logKey, [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'path' => $request->path(),
            'method' => $request->method(),
            'timestamp' => now()->toISOString(),
            'user_id' => $request->user()?->id,
        ], now()->addDay());
    }
}
