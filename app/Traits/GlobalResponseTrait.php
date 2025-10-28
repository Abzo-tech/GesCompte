<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Trait GlobalResponseTrait
 *
 * Trait global pour standardiser le format des réponses API
 * Utilisé dans tous les contrôleurs API
 */
trait GlobalResponseTrait
{
    /**
     * Format de réponse standardisé pour les succès
     *
     * @param mixed $data Données à retourner
     * @param string $message Message de succès
     * @param int $code Code HTTP
     * @return JsonResponse
     */
    protected function successResponse($data = null, string $message = 'Opération réussie', int $code = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $code);
    }

    /**
     * Format de réponse standardisé pour les erreurs
     *
     * @param string $message Message d'erreur
     * @param int $code Code HTTP
     * @param array $details Détails de l'erreur
     * @return JsonResponse
     */
    protected function errorResponse(string $message = 'Une erreur est survenue', int $code = 500, array $details = []): JsonResponse
    {
        $response = [
            'success' => false,
            'error' => [
                'code' => $this->getErrorCode($code),
                'message' => $message,
            ]
        ];

        if (!empty($details)) {
            $response['error']['details'] = $details;
        }

        return response()->json($response, $code);
    }

    /**
     * Format de réponse paginée
     *
     * @param LengthAwarePaginator $paginator
     * @param string $message
     * @return JsonResponse
     */
    protected function paginatedResponse(LengthAwarePaginator $paginator, string $message = 'Données récupérées avec succès'): JsonResponse
    {
        $data = $paginator->getCollection();

        $response = [
            'success' => true,
            'data' => $data,
            'pagination' => [
                'currentPage' => $paginator->currentPage(),
                'totalPages' => $paginator->lastPage(),
                'totalItems' => $paginator->total(),
                'itemsPerPage' => $paginator->perPage(),
                'hasNext' => $paginator->hasMorePages(),
                'hasPrevious' => $paginator->currentPage() > 1,
            ],
            'links' => [
                'self' => $paginator->url($paginator->currentPage()),
                'first' => $paginator->url(1),
                'last' => $paginator->url($paginator->lastPage()),
            ]
        ];

        if ($paginator->hasMorePages()) {
            $response['links']['next'] = $paginator->nextPageUrl();
        }

        if ($paginator->currentPage() > 1) {
            $response['links']['previous'] = $paginator->previousPageUrl();
        }

        return response()->json($response, 200);
    }

    /**
     * Format de réponse pour les validations
     *
     * @param array $errors Erreurs de validation
     * @return JsonResponse
     */
    protected function validationErrorResponse(array $errors): JsonResponse
    {
        return $this->errorResponse(
            'Erreurs de validation des données',
            422,
            $errors
        );
    }

    /**
     * Format de réponse pour les ressources non trouvées
     *
     * @param string $resource Nom de la ressource
     * @return JsonResponse
     */
    protected function notFoundResponse(string $resource = 'Ressource'): JsonResponse
    {
        return $this->errorResponse(
            "{$resource} non trouvée",
            404
        );
    }

    /**
     * Format de réponse pour les accès non autorisés
     *
     * @param string $message
     * @return JsonResponse
     */
    protected function unauthorizedResponse(string $message = 'Accès non autorisé'): JsonResponse
    {
        return $this->errorResponse($message, 403);
    }

    /**
     * Obtenir le code d'erreur en fonction du code HTTP
     *
     * @param int $httpCode
     * @return string
     */
    private function getErrorCode(int $httpCode): string
    {
        return match ($httpCode) {
            400 => 'BAD_REQUEST',
            401 => 'UNAUTHORIZED',
            403 => 'FORBIDDEN',
            404 => 'NOT_FOUND',
            422 => 'VALIDATION_ERROR',
            429 => 'RATE_LIMITED',
            500 => 'INTERNAL_SERVER_ERROR',
            default => 'ERROR',
        };
    }
}
