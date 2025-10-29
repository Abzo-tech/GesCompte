<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Compte;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(
 *     name="Comptes",
 *     description="Gestion des comptes bancaires"
 * )
 */
class CompteController extends Controller
{
    /**
     * @OA\Get(
     *     path="/dieng/v1/comptes",
     *     summary="Lister tous les comptes",
     *     tags={"Comptes"},
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Filtrer par type de compte (courant, epargne, cheque)",
     *         @OA\Schema(type="string", enum={"courant", "epargne", "cheque"})
     *     ),
     *     @OA\Parameter(
     *         name="statut",
     *         in="query",
     *         description="Filtrer par statut (actif, bloque, ferme)",
     *         @OA\Schema(type="string", enum={"actif", "bloque", "ferme"})
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Recherche par numéro de compte ou nom/prénom du client",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="client_id",
     *         in="query",
     *         description="Filtrer par identifiant du client (UUID)",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Parameter(
     *         name="client_phone",
     *         in="query",
     *         description="Filtrer par numéro de téléphone du client",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         description="Champ de tri (date_creation, solde, numero)",
     *         @OA\Schema(type="string", default="date_creation")
     *     ),
     *     @OA\Parameter(
     *         name="order",
     *         in="query",
     *         description="Ordre de tri (asc, desc)",
     *         @OA\Schema(type="string", enum={"asc", "desc"}, default="asc")
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Nombre d'éléments par page (1-100)",
     *         @OA\Schema(type="integer", minimum=1, maximum=100, default=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Liste des comptes récupérée avec succès",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Compte")
     *             ),
     *             @OA\Property(
     *                 property="pagination",
     *                 type="object",
     *                 @OA\Property(property="currentPage", type="integer", example=1),
     *                 @OA\Property(property="totalPages", type="integer", example=1),
     *                 @OA\Property(property="totalItems", type="integer", example=10),
     *                 @OA\Property(property="itemsPerPage", type="integer", example=10),
     *                 @OA\Property(property="hasNext", type="boolean", example=false),
     *                 @OA\Property(property="hasPrevious", type="boolean", example=false)
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Démarrer la requête avec les relations nécessaires
            $query = Compte::with('client')
                ->whereNull('deleted_at'); // Ne pas inclure les comptes supprimés

            // Filtres
            $this->applyFilters($query, $request);

            // Trier les résultats
            $sortField = $request->input('sort', 'date_creation');
            $sortOrder = $request->input('order', 'asc');
            $query->orderBy($sortField, $sortOrder);

            // Pagination
            $perPage = min($request->input('limit', 10), 100); // Max 100 par page
            $comptes = $query->paginate($perPage);

            // Formater la réponse
            return response()->json([
                'success' => true,
                'data' => $comptes->items(),
                'pagination' => [
                    'currentPage' => $comptes->currentPage(),
                    'totalPages' => $comptes->lastPage(),
                    'totalItems' => $comptes->total(),
                    'itemsPerPage' => $comptes->perPage(),
                    'hasNext' => $comptes->hasMorePages(),
                    'hasPrevious' => $comptes->currentPage() > 1,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la récupération des comptes.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Applique les filtres de recherche à la requête
     */
    protected function applyFilters($query, $request): void
    {
        // Filtre par type de compte
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filtre par statut
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        // Recherche par numéro de compte ou nom du client
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('numero', 'like', "%{$search}%")
                  ->orWhereHas('client', function($q) use ($search) {
                      $q->where('nom', 'like', "%{$search}%")
                        ->orWhere('prenom', 'like', "%{$search}%");
                  });
            });
        }

        // Filtre par ID client
        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        // Filtre par téléphone client
        if ($request->filled('client_phone')) {
            $query->whereHas('client', function($q) use ($request) {
                $q->where('telephone', $request->client_phone);
            });
        }
    }
}
