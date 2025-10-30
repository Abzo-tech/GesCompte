<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\TokenRepository;
use Symfony\Component\HttpFoundation\Response;

class AuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check for Bearer token in Authorization header
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'UNAUTHORIZED',
                    'message' => 'Access token required'
                ]
            ], 401);
        }

        // For Passport, we need to authenticate using the token
        // Check if token exists in database using TokenRepository
        $tokenRepository = app(TokenRepository::class);
        $accessToken = $tokenRepository->find($token);

        if (!$accessToken || $accessToken->revoked || $accessToken->expires_at < now()) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'INVALID_TOKEN',
                    'message' => 'Invalid or expired access token'
                ]
            ], 401);
        }

        // Get the user associated with the token
        $user = $accessToken->user;

        if (!$user) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'INVALID_TOKEN',
                    'message' => 'Invalid or expired access token'
                ]
            ], 401);
        }

        // Check if user is active
        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'ACCOUNT_INACTIVE',
                    'message' => 'User account is inactive'
                ]
            ], 403);
        }

        // Set the authenticated user
        Auth::setUser($user);
        $request->merge(['user' => $user]);

        return $next($request);
    }
}
