<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LoggingMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);

        // Log the incoming request
        \Log::info('API Request Started', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'host' => $request->getHost(),
            'operation' => $this->getOperationName($request),
            'timestamp' => now()->toISOString()
        ]);

        $response = $next($request);

        $endTime = microtime(true);
        $duration = round(($endTime - $startTime) * 1000, 2); // in milliseconds

        // Log the response
        \Log::info('API Request Completed', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'status_code' => $response->getStatusCode(),
            'duration_ms' => $duration,
            'operation' => $this->getOperationName($request),
            'timestamp' => now()->toISOString()
        ]);

        return $response;
    }

    /**
     * Get operation name from request
     */
    private function getOperationName(Request $request): string
    {
        $method = $request->method();
        $path = $request->path();

        // Extract operation from path
        $segments = explode('/', $path);
        $resource = $segments[count($segments) - 2] ?? 'unknown';
        $action = $segments[count($segments) - 1] ?? 'unknown';

        if (is_numeric($action)) {
            $action = 'show';
        }

        return $method . ' ' . $resource . '/' . $action;
    }
}
