<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Compte;
use App\Http\Resources\CompteResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Schema(
 *     schema="CompteRequest",
 *     title="Compte Request",
 *     description="Compte creation/update request",
 *     @OA\Property(property="numero", type="string", nullable=true, description="Numéro du compte"),
 *     @OA\Property(property="type", type="string", enum={"courant","epargne","cheque"}, description="Type de compte"),
 *     @OA\Property(property="statut", type="string", enum={"actif","bloque","ferme"}, nullable=true, description="Statut du compte"),
 *     @OA\Property(property="client_id", type="string", format="uuid", nullable=true, description="ID du client"),
 *     @OA\Property(property="devise", type="string", maxLength=4, description="Devise du compte"),
 *     @OA\Property(property="date_creation", type="string", format="date-time", description="Date de création")
 * )
 */

/**
 * Définitions des schémas déplacées dans SchemaDefinitions.php
 */

class CompteController extends Controller
{
    /**
     * @OA\Get(
     *     path="/dieng/v1/comptes",
     *     summary="Lister tous les comptes",
     *     tags={"Comptes"},
     *     security={{"bearerAuth": {}}},
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
     *         description="Champ de tri (dateCreation, solde, titulaire)",
     *         @OA\Schema(type="string", enum={"dateCreation", "solde", "titulaire"}, default="dateCreation")
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
     *         @OA\JsonContent(ref="#/components/schemas/CompteApiCollection")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Requête invalide",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=429,
     *         description="Trop de requêtes",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Too Many Attempts.")
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Compte::with('client');

            // Filtres explicites
            if ($request->filled('type')) {
                $query->where('type', $request->type);
            }

            if ($request->filled('statut')) {
                $query->where('statut', $request->statut);
            }

            if ($request->filled('client_id')) {
                $query->where('client_id', $request->client_id);
            }

            if ($request->filled('client_phone')) {
                $query->client($request->client_phone);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('numero', 'like', "%{$search}%")
                      ->orWhereHas('client', function ($clientQuery) use ($search) {
                          $clientQuery->where('nom', 'like', "%{$search}%")
                                     ->orWhere('prenom', 'like', "%{$search}%");
                      });
                });
            }

            // Filtres par défaut si non fournis
            if (!$request->has('type') && !$request->has('statut')) {
                $query->whereIn('type', ['cheque', 'epargne'])
                      ->where('statut', 'actif');
            }

            // Tri
            $sort = $request->input('sort');
            $order = strtolower($request->input('order', 'asc')) === 'desc' ? 'desc' : 'asc';
            if ($sort === 'dateCreation') {
                $query->orderBy('date_creation', $order);
            } elseif ($sort === 'titulaire') {
                $query->leftJoin('clients', 'clients.id', '=', 'comptes.client_id')
                      ->orderBy('clients.prenom', $order)
                      ->orderBy('clients.nom', $order)
                      ->select('comptes.*');
            } elseif ($sort === 'solde') {
                // Tri par solde non supporté nativement ici; fallback
                $query->orderBy('id', $order);
            }

            // Pagination
            $comptes = $query->paginate($request->input('limit', 10))->appends($request->query());

            // Resources
            $data = CompteResource::collection($comptes->getCollection());

            // Links
            $links = [
                'self' => $request->fullUrl(),
                'next' => $comptes->nextPageUrl(),
                'previous' => $comptes->previousPageUrl(),
                'first' => $comptes->url(1),
                'last' => $comptes->url($comptes->lastPage()),
            ];

            return response()->json([
                'success' => true,
                'data' => $data,
                'pagination' => [
                    'currentPage' => $comptes->currentPage(),
                    'totalPages' => $comptes->lastPage(),
                    'totalItems' => $comptes->total(),
                    'itemsPerPage' => $comptes->perPage(),
                    'hasNext' => $comptes->hasMorePages(),
                    'hasPrevious' => $comptes->currentPage() > 1,
                ],
                'links' => $links,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => 'Erreur lors de la récupération des comptes: ' . $e->getMessage()
                ]
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/dieng/v1/comptes",
     *     summary="Créer un nouveau compte",
     *     tags={"Comptes"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="numero", type="string", nullable=true, description="Numéro du compte"),
     *             @OA\Property(property="type", type="string", enum={"courant","epargne","cheque"}, description="Type de compte"),
     *             @OA\Property(property="statut", type="string", enum={"actif","bloque","ferme"}, nullable=true, description="Statut du compte"),
     *             @OA\Property(property="client_id", type="string", format="uuid", nullable=true, description="ID du client"),
     *             @OA\Property(property="devise", type="string", maxLength=4, description="Devise du compte"),
     *             @OA\Property(property="date_creation", type="string", format="date-time", description="Date de création")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Compte créé",
     *         @OA\JsonContent(ref="#/components/schemas/Compte")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreurs de validation"
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'numero' => 'nullable|string|unique:comptes,numero',
                'type' => 'required|in:courant,epargne,cheque',
                'statut' => 'in:actif,bloque,ferme',
                'client_id' => 'nullable|exists:clients,id',
                'devise' => 'required|string|max:4',
                'date_creation' => 'required|date'
            ]);

            // Définir les valeurs par défaut
            $validated['statut'] = $validated['statut'] ?? 'actif';
            $validated['devise'] = $validated['devise'] ?? 'FCFA';

            $compte = Compte::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Compte créé avec succès',
                'data' => $compte->load('client')
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'Erreurs de validation',
                    'details' => $e->errors()
                ]
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'CREATION_ERROR',
                    'message' => 'Erreur lors de la création du compte: ' . $e->getMessage()
                ]
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/dieng/v1/comptes/{id}",
     *     summary="Obtenir un compte spécifique",
     *     tags={"Comptes"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Détails du compte",
     *         @OA\JsonContent(ref="#/components/schemas/Compte")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Compte non trouvé"
     *     )
     * )
     */
    public function show($id): JsonResponse
    {
        try {
            $compte = Compte::with('client')->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $compte
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'COMPTE_NOT_FOUND',
                    'message' => 'Compte non trouvé'
                ]
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'SHOW_ERROR',
                    'message' => $e->getMessage()
                ]
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/dieng/v1/comptes/{id}",
     *     summary="Mettre à jour un compte",
     *     tags={"Comptes"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="numero", type="string", nullable=true, description="Numéro du compte"),
     *             @OA\Property(property="type", type="string", enum={"courant","epargne","cheque"}, description="Type de compte"),
     *             @OA\Property(property="statut", type="string", enum={"actif","bloque","ferme"}, nullable=true, description="Statut du compte"),
     *             @OA\Property(property="client_id", type="string", format="uuid", nullable=true, description="ID du client"),
     *             @OA\Property(property="devise", type="string", maxLength=4, description="Devise du compte"),
     *             @OA\Property(property="date_creation", type="string", format="date-time", description="Date de création")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Compte mis à jour",
     *         @OA\JsonContent(ref="#/components/schemas/Compte")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreurs de validation"
     *     )
     * )
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'type' => 'in:courant,epargne,cheque',
                'statut' => 'in:actif,bloque,ferme',
                'devise' => 'string|max:3'
            ]);

            $compte = Compte::findOrFail($id);
            $compte->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Compte mis à jour avec succès',
                'data' => $compte->load('client')
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'COMPTE_NOT_FOUND',
                    'message' => 'Compte non trouvé'
                ]
            ], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'Erreurs de validation',
                    'details' => $e->errors()
                ]
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'UPDATE_ERROR',
                    'message' => $e->getMessage()
                ]
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/dieng/v1/comptes/{id}",
     *     summary="Supprimer un compte (soft delete)",
     *     tags={"Comptes"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Compte archivé"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Compte non trouvé"
     *     )
     * )
     */
    public function destroy($id): JsonResponse
    {
        try {
            $compte = Compte::findOrFail($id);
            $compte->delete();

            return response()->json([
                'success' => true,
                'message' => 'Compte archivé avec succès',
                'data' => [
                    'compte_id' => $compte->id,
                    'numero' => $compte->numero,
                    'status' => 'archived'
                ]
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'COMPTE_NOT_FOUND',
                    'message' => 'Compte non trouvé'
                ]
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'ARCHIVE_ERROR',
                    'message' => $e->getMessage()
                ]
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/dieng/v1/comptes/archives/list",
     *     summary="Lister les comptes archivés",
     *     tags={"Comptes"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Filtrer par type de compte (courant, epargne, cheque)",
     *         @OA\Schema(type="string", enum={"courant", "epargne", "cheque"})
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Recherche par numéro de compte ou nom/prénom du client",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         description="Champ de tri (dateCreation, solde, titulaire)",
     *         @OA\Schema(type="string", enum={"dateCreation", "solde", "titulaire"}, default="dateCreation")
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
     *         description="Liste des comptes archivés récupérée avec succès",
     *         @OA\JsonContent(ref="#/components/schemas/CompteApiCollection")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Requête invalide",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=429,
     *         description="Trop de requêtes",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Too Many Attempts.")
     *         )
     *     )
     * )
     */
    public function archives(Request $request): JsonResponse
    {
        try {
            $query = Compte::onlyTrashed()->with('client');

            if ($request->has('type')) {
                $query->where('type', $request->type);
            }

            $comptes = $query->paginate($request->input('limit', 10))->appends($request->query());

            $data = CompteResource::collection($comptes->getCollection());
            $links = [
                'self' => $request->fullUrl(),
                'next' => $comptes->nextPageUrl(),
                'previous' => $comptes->previousPageUrl(),
                'first' => $comptes->url(1),
                'last' => $comptes->url($comptes->lastPage()),
            ];

            return response()->json([
                'success' => true,
                'data' => $data,
                'pagination' => [
                    'currentPage' => $comptes->currentPage(),
                    'totalPages' => $comptes->lastPage(),
                    'totalItems' => $comptes->total(),
                    'itemsPerPage' => $comptes->perPage(),
                    'hasNext' => $comptes->hasMorePages(),
                    'hasPrevious' => $comptes->currentPage() > 1,
                ],
                'links' => $links,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'ARCHIVE_ERROR',
                    'message' => 'Erreur lors de la récupération des comptes archivés: ' . $e->getMessage()
                ]
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/dieng/v1/comptes/{id}/restore",
     *     summary="Restaurer un compte archivé",
     *     tags={"Comptes"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Compte restauré",
     *         @OA\JsonContent(ref="#/components/schemas/Compte")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Compte archivé non trouvé"
     *     )
     * )
     */
    public function restore($id): JsonResponse
    {
        try {
            $compte = Compte::onlyTrashed()->findOrFail($id);
            $compte->restore();

            return response()->json([
                'success' => true,
                'message' => 'Compte restauré avec succès',
                'data' => $compte->load('client')
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'COMPTE_NOT_FOUND',
                    'message' => 'Compte archivé non trouvé'
                ]
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'RESTORE_ERROR',
                    'message' => 'Erreur lors de la restauration du compte: ' . $e->getMessage()
                ]
            ], 500);
        }
    }
}
