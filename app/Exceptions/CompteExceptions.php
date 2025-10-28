<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

/**
 * Exception personnalisée pour les comptes non trouvés
 */
class CompteNotFoundException extends Exception
{
    public function __construct(string $message = 'Compte non trouvé')
    {
        parent::__construct($message, 404);
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'error' => [
                'code' => 'COMPTE_NOT_FOUND',
                'message' => $this->getMessage(),
            ]
        ], 404);
    }
}

/**
 * Exception personnalisée pour les comptes bloqués
 */
class CompteBloqueException extends Exception
{
    public function __construct(string $message = 'Ce compte est bloqué')
    {
        parent::__construct($message, 403);
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'error' => [
                'code' => 'COMPTE_BLOQUE',
                'message' => $this->getMessage(),
            ]
        ], 403);
    }
}

/**
 * Exception personnalisée pour solde insuffisant
 */
class SoldeInsuffisantException extends Exception
{
    public function __construct(string $message = 'Solde insuffisant pour cette opération')
    {
        parent::__construct($message, 400);
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'error' => [
                'code' => 'SOLDE_INSUFFISANT',
                'message' => $this->getMessage(),
            ]
        ], 400);
    }
}

/**
 * Exception personnalisée pour les accès non autorisés
 */
class UnauthorizedCompteAccessException extends Exception
{
    public function __construct(string $message = 'Accès non autorisé à ce compte')
    {
        parent::__construct($message, 403);
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'error' => [
                'code' => 'UNAUTHORIZED_ACCESS',
                'message' => $this->getMessage(),
            ]
        ], 403);
    }
}

/**
 * Exception personnalisée pour les limites de taux
 */
class RateLimitExceededException extends Exception
{
    public function __construct(string $message = 'Limite de taux d\'interrogation dépassée')
    {
        parent::__construct($message, 429);
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'error' => [
                'code' => 'RATE_LIMIT_EXCEEDED',
                'message' => $this->getMessage(),
            ]
        ], 429);
    }
}
