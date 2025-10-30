<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Compte;
use App\Models\Client;
use App\Http\Resources\CompteResource;
use App\Http\Requests\StoreCompteRequest;
use App\Events\SendClientNotification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

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
     *     description="Récupère la liste des comptes avec pagination, filtrage et tri",
     *     tags={"Comptes"},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Numéro de page",
     *         @OA\Schema(type="integer", default=1, minimum=1)
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Nombre d'éléments par page (max: 100)",
     *         @OA\Schema(type="integer", default=10, minimum=1, maximum=100)
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Filtrer par type de compte (epargne ou cheque)",
     *         @OA\Schema(type="string", enum={"epargne", "cheque"})
     *     ),
     *     @OA\Parameter(
     *         name="statut",
     *         in="query",
     *         description="Filtrer par statut du compte",
     *         @OA\Schema(type="string", enum={"actif", "bloque", "ferme"})
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Recherche par numéro de compte ou nom/prénom du titulaire",
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
     *                 @OA\Property(property="totalPages", type="integer", example=5),
     *                 @OA\Property(property="totalItems", type="integer", example=50),
     *                 @OA\Property(property="itemsPerPage", type="integer", example=10),
     *                 @OA\Property(property="hasNext", type="boolean", example=true),
     *                 @OA\Property(property="hasPrevious", type="boolean", example=false)
     *             ),
     *             @OA\Property(
     *                 property="links",
     *                 type="object",
     *                 @OA\Property(property="self", type="string", example="/dieng/v1/comptes?page=1"),
     *                 @OA\Property(property="next", type="string", example="/dieng/v1/comptes?page=2"),
     *                 @OA\Property(property="previous", type="string", nullable=true, example=null),
     *                 @OA\Property(property="first", type="string", example="/dieng/v1/comptes?page=1"),
     *                 @OA\Property(property="last", type="string", example="/dieng/v1/comptes?page=5")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Requête invalide",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="object",
     *                 @OA\Property(property="code", type="string", example="INVALID_REQUEST"),
     *                 @OA\Property(property="message", type="string", example="La requête est invalide"),
     *                 @OA\Property(property="details", type="object", example={"champ": ["Le champ est requis"]})
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=429,
     *         description="Trop de requêtes",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="object",
     *                 @OA\Property(property="code", type="string", example="TOO_MANY_REQUESTS"),
     *                 @OA\Property(property="message", type="string", example="Trop de requêtes. Veuillez réessayer plus tard.")
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Vérifier si la table des comptes existe
            if (!\Schema::hasTable('comptes')) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'DATABASE_ERROR',
                        'message' => 'La table des comptes n\'existe pas. Veuillez exécuter les migrations.'
                    ]
                ], 500);
            }
            $perPage = min($request->input('limit', 10), 100); // Limite à 100 par page
            $page = $request->input('page', 1);

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
                $query->whereHas('client', function($q) use ($request) {
                    $q->where('telephone', $request->client_phone);
                });
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('numero', 'ilike', "%{$search}%")
                      ->orWhereHas('client', function ($clientQuery) use ($search) {
                          $clientQuery->where('nom', 'ilike', "%{$search}%")
                                      ->orWhere('prenom', 'ilike', "%{$search}%");
                      });
                });
            }

            // Filtres par défaut si non fournis
            if (!$request->has('type') && !$request->has('statut')) {
                $query->whereIn('type', ['cheque', 'epargne'])
                      ->where('statut', 'actif');
            }

            // Tri
            $sort = $request->input('sort', 'dateCreation');
            $order = strtolower($request->input('order', 'asc')) === 'desc' ? 'desc' : 'asc';

            switch ($sort) {
                case 'dateCreation':
                    $query->orderBy('date_creation', $order);
                    break;
                case 'solde':
                    $query->orderBy('solde', $order);
                    break;
                case 'titulaire':
                    $query->leftJoin('clients', 'clients.id', '=', 'comptes.client_id')
                          ->orderBy('clients.nom', $order)
                          ->orderBy('clients.prenom', $order)
                          ->select('comptes.*');
                    break;
                default:
                    $query->orderBy('date_creation', $order);
            }

            // Pagination
            $comptes = $query->paginate($perPage, ['*'], 'page', $page);

            // Formatage de la réponse
            return response()->json([
                'success' => true,
                'data' => CompteResource::collection($comptes),
                'pagination' => [
                    'currentPage' => $comptes->currentPage(),
                    'totalPages' => $comptes->lastPage(),
                    'totalItems' => $comptes->total(),
                    'itemsPerPage' => $comptes->perPage(),
                    'hasNext' => $comptes->hasMorePages(),
                    'hasPrevious' => $comptes->currentPage() > 1,
                ],
                'links' => [
                    'self' => $comptes->url($page),
                    'next' => $comptes->nextPageUrl(),
                    'previous' => $comptes->previousPageUrl(),
                    'first' => $comptes->url(1),
                    'last' => $comptes->url($comptes->lastPage()),
                ]
            ]);
        } catch (\Exception $e) {
            // Journaliser l'erreur
            \Log::error('Erreur lors de la récupération des comptes: ' . $e->getMessage());

            // Retourner une réponse d'erreur
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => 'Une erreur est survenue lors de la récupération des comptes.',
                    'details' => config('app.debug') ? $e->getMessage() : null
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
     *             required={"type", "devise", "client"},
     *             @OA\Property(property="type", type="string", enum={"courant","epargne","cheque"}, description="Type de compte"),
     *             @OA\Property(property="statut", type="string", enum={"actif","bloque","ferme"}, nullable=true, description="Statut du compte", default="actif"),
     *             @OA\Property(property="soldeInitial", type="number", minimum=10000, description="Solde initial (minimum 10 000)"),
     *             @OA\Property(property="devise", type="string", maxLength=4, description="Devise du compte"),
     *             @OA\Property(
     *                 property="client",
     *                 type="object",
     *                 required={"titulaire", "nci", "email", "telephone", "adresse"},
     *                 @OA\Property(property="id", type="string", format="uuid", nullable=true, description="ID du client existant"),
     *                 @OA\Property(property="titulaire", type="string", description="Nom complet du titulaire"),
     *                 @OA\Property(property="nci", type="string", description="Numéro NCI sénégalais"),
     *                 @OA\Property(property="email", type="string", format="email", description="Adresse email"),
     *                 @OA\Property(property="telephone", type="string", description="Numéro de téléphone sénégalais"),
     *                 @OA\Property(property="adresse", type="string", description="Adresse du client")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Compte créé avec succès",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Compte créé avec succès"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="string", format="uuid", example="660f9511-f30c-52e5-b827-557766551111"),
     *                 @OA\Property(property="numeroCompte", type="string", example="C00123460"),
     *                 @OA\Property(property="titulaire", type="string", example="Cheikh Sy"),
     *                 @OA\Property(property="type", type="string", example="cheque"),
     *                 @OA\Property(property="solde", type="number", example=500000),
     *                 @OA\Property(property="devise", type="string", example="FCFA"),
     *                 @OA\Property(property="dateCreation", type="string", format="date-time", example="2025-10-19T10:30:00Z"),
     *                 @OA\Property(property="statut", type="string", example="actif"),
     *                 @OA\Property(
     *                     property="metadata",
     *                     type="object",
     *                     @OA\Property(property="derniereModification", type="string", format="date-time"),
     *                     @OA\Property(property="version", type="integer", example=1)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Erreurs de validation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(
     *                 property="error",
     *                 type="object",
     *                 @OA\Property(property="code", type="string", example="VALIDATION_ERROR"),
     *                 @OA\Property(property="message", type="string", example="Les données fournies sont invalides"),
     *                 @OA\Property(
     *                     property="details",
     *                     type="object",
     *                     example={"client.titulaire": {"Le nom du titulaire est requis"}}
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(
     *                 property="error",
     *                 type="object",
     *                 @OA\Property(property="code", type="string", example="CREATION_ERROR"),
     *                 @OA\Property(property="message", type="string", example="Erreur lors de la création du compte")
     *             )
     *         )
     *     )
     * )
     */
    public function store(StoreCompteRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $validated = $request->validated();

            // Handle client creation or retrieval
            $client = $this->handleClient($validated['client']);

            // Create account
            $compteData = [
                'type' => $validated['type'],
                'statut' => $validated['statut'] ?? 'actif',
                'client_id' => $client->id,
                'devise' => $validated['devise'],
                'date_creation' => now(),
            ];

            // Add initial balance if provided
            if (isset($validated['soldeInitial'])) {
                // Create initial deposit transaction
                $compte = Compte::create($compteData);

                // Create initial deposit transaction
                $compte->transactions()->create([
                    'reference' => \App\Models\Transaction::generateReference(),
                    'type' => 'depot',
                    'montant' => $validated['soldeInitial'],
                    'description' => 'Solde initial lors de la création du compte',
                    'date_transaction' => now(),
                ]);
            } else {
                $compte = Compte::create($compteData);
            }

            // Send notifications
            event(new SendClientNotification($client, 'both'));

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Compte créé avec succès',
                'data' => [
                    'id' => $compte->id,
                    'numeroCompte' => $compte->numero,
                    'titulaire' => $client->full_name,
                    'type' => $compte->type,
                    'solde' => $compte->solde,
                    'devise' => $compte->devise,
                    'dateCreation' => $compte->date_creation->toISOString(),
                    'statut' => $compte->statut,
                    'metadata' => [
                        'derniereModification' => $compte->updated_at->toISOString(),
                        'version' => 1
                    ]
                ]
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'Les données fournies sont invalides',
                    'details' => $e->errors()
                ]
            ], 400);
        } catch (\Exception $e) {
            DB::rollBack();
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
     * Handle client creation or retrieval
     */
    private function handleClient(array $clientData): Client
    {
        if (isset($clientData['id'])) {
            // Use existing client
            return Client::findOrFail($clientData['id']);
        } else {
            // Create new client
            return Client::create([
                'nom' => $clientData['titulaire'],
                'prenom' => '', // Assuming full name is provided, split if needed
                'email' => $clientData['email'],
                'telephone' => $clientData['telephone'],
                'adresse' => $clientData['adresse'],
                'statut' => 'actif'
            ]);
        }
    }

    /**
     * @OA\Get(
     *     path="/dieng/v1/comptes/{compteId}",
     *     summary="Récupérer un compte spécifique",
     *     description="Permet à l'admin et au client de récupérer les détails d'un compte spécifique. Recherche d'abord en local (comptes cheque/epargne actifs), puis en serverless si non trouvé.",
     *     tags={"Comptes"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="compteId",
     *         in="path",
     *         required=true,
     *         description="ID du compte à récupérer",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Détails du compte récupérés avec succès",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000"),
     *                 @OA\Property(property="numeroCompte", type="string", example="C00123456"),
     *                 @OA\Property(property="titulaire", type="string", example="Amadou Diallo"),
     *                 @OA\Property(property="type", type="string", enum={"courant","epargne","cheque"}, example="epargne"),
     *                 @OA\Property(property="solde", type="number", example=1250000),
     *                 @OA\Property(property="devise", type="string", example="FCFA"),
     *                 @OA\Property(property="dateCreation", type="string", format="date-time", example="2023-03-15T00:00:00Z"),
     *                 @OA\Property(property="statut", type="string", enum={"actif","bloque","ferme"}, example="bloque"),
     *                 @OA\Property(property="motifBlocage", type="string", nullable=true, example="Inactivité de 30+ jours"),
     *                 @OA\Property(
     *                     property="metadata",
     *                     type="object",
     *                     @OA\Property(property="derniereModification", type="string", format="date-time"),
     *                     @OA\Property(property="version", type="integer", example=1)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Compte non trouvé",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(
     *                 property="error",
     *                 type="object",
     *                 @OA\Property(property="code", type="string", example="COMPTE_NOT_FOUND"),
     *                 @OA\Property(property="message", type="string", example="Le compte avec l'ID spécifié n'existe pas"),
     *                 @OA\Property(
     *                     property="details",
     *                     type="object",
     *                     @OA\Property(property="compteId", type="string", example="550e8400-e29b-41d4-a716-446655440000")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(
     *                 property="error",
     *                 type="object",
     *                 @OA\Property(property="code", type="string", example="INTERNAL_ERROR"),
     *                 @OA\Property(property="message", type="string", example="Erreur interne du serveur")
     *             )
     *         )
     *     )
     * )
     */
    public function show($compteId): JsonResponse
    {
        try {
            // Recherche locale d'abord (comptes cheque/epargne actifs)
            $compte = Compte::where('id', $compteId)
                           ->whereIn('type', ['cheque', 'epargne'])
                           ->where('statut', 'actif')
                           ->with('client')
                           ->first();

            // Si non trouvé en local, rechercher en serverless (tous les comptes)
            if (!$compte) {
                $compte = Compte::with('client')->find($compteId);

                // Log pour indiquer que la recherche serverless a été utilisée
                if ($compte) {
                    \Log::info("Compte trouvé via recherche serverless: {$compteId}");
                }
            }

            if (!$compte) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'COMPTE_NOT_FOUND',
                        'message' => 'Le compte avec l\'ID spécifié n\'existe pas',
                        'details' => [
                            'compteId' => $compteId
                        ]
                    ]
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => new CompteResource($compte)
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur lors de la récupération du compte: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => 'Erreur interne du serveur',
                    'details' => config('app.debug') ? $e->getMessage() : null
                ]
            ], 500);
        }
    }

    /**
     * @OA\Patch(
     *     path="/dieng/v1/comptes/{compteId}",
     *     summary="Mettre à jour les informations du client",
     *     description="Permet de mettre à jour les informations du client associé à un compte. Tous les champs sont optionnels mais au moins un champ doit être fourni.",
     *     tags={"Comptes"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="compteId",
     *         in="path",
     *         required=true,
     *         description="ID du compte dont on veut modifier les informations client",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="titulaire", type="string", description="Nouveau nom du titulaire"),
     *             @OA\Property(
     *                 property="informationsClient",
     *                 type="object",
     *                 description="Informations du client à mettre à jour",
     *                 @OA\Property(property="telephone", type="string", description="Nouveau numéro de téléphone sénégalais"),
     *                 @OA\Property(property="email", type="string", format="email", description="Nouvelle adresse email"),
     *                 @OA\Property(property="password", type="string", description="Nouveau mot de passe"),
     *                 @OA\Property(property="nci", type="string", description="Nouveau numéro NCI sénégalais")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Informations du client mises à jour avec succès",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Informations du client mises à jour avec succès"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000"),
     *                 @OA\Property(property="numeroCompte", type="string", example="C00123456"),
     *                 @OA\Property(property="titulaire", type="string", example="Amadou Diallo Junior"),
     *                 @OA\Property(property="type", type="string", enum={"courant","epargne","cheque"}, example="epargne"),
     *                 @OA\Property(property="solde", type="number", example=1250000),
     *                 @OA\Property(property="devise", type="string", example="FCFA"),
     *                 @OA\Property(property="dateCreation", type="string", format="date-time", example="2023-03-15T00:00:00Z"),
     *                 @OA\Property(property="statut", type="string", enum={"actif","bloque","ferme"}, example="bloque"),
     *                 @OA\Property(
     *                     property="metadata",
     *                     type="object",
     *                     @OA\Property(property="derniereModification", type="string", format="date-time"),
     *                     @OA\Property(property="version", type="integer", example=1)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Erreurs de validation ou aucun champ fourni",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(
     *                 property="error",
     *                 type="object",
     *                 @OA\Property(property="code", type="string", example="VALIDATION_ERROR"),
     *                 @OA\Property(property="message", type="string", example="Au moins un champ doit être fourni pour la mise à jour"),
     *                 @OA\Property(
     *                     property="details",
     *                     type="object",
     *                     example={"general": {"Au moins un champ doit être fourni pour la mise à jour"}}
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Compte non trouvé",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(
     *                 property="error",
     *                 type="object",
     *                 @OA\Property(property="code", type="string", example="COMPTE_NOT_FOUND"),
     *                 @OA\Property(property="message", type="string", example="Le compte avec l'ID spécifié n'existe pas")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(
     *                 property="error",
     *                 type="object",
     *                 @OA\Property(property="code", type="string", example="INTERNAL_ERROR"),
     *                 @OA\Property(property="message", type="string", example="Erreur interne du serveur")
     *             )
     *         )
     *     )
     * )
     */
    public function update(\App\Http\Requests\UpdateClientRequest $request, $compteId): JsonResponse
    {
        try {
            // Récupérer le compte
            $compte = Compte::with('client')->findOrFail($compteId);

            $validated = $request->validated();

            // Préparer les données de mise à jour du client
            $clientData = [];

            if (isset($validated['titulaire'])) {
                // Si titulaire est fourni, on suppose que c'est "Prénom Nom"
                $parts = explode(' ', $validated['titulaire'], 2);
                $clientData['prenom'] = $parts[0] ?? '';
                $clientData['nom'] = $parts[1] ?? $parts[0] ?? '';
            }

            if (isset($validated['informationsClient'])) {
                $clientInfo = $validated['informationsClient'];

                if (isset($clientInfo['telephone'])) {
                    $clientData['telephone'] = $clientInfo['telephone'];
                }

                if (isset($clientInfo['email'])) {
                    $clientData['email'] = $clientInfo['email'];
                }

                if (isset($clientInfo['password'])) {
                    $clientData['password'] = bcrypt($clientInfo['password']);
                }

                if (isset($clientInfo['nci'])) {
                    $clientData['nci'] = $clientInfo['nci'];
                }
            }

            // Mettre à jour le client si des données sont fournies
            if (!empty($clientData)) {
                $compte->client->update($clientData);
            }

            // Recharger le compte avec les données mises à jour
            $compte->load('client');

            return response()->json([
                'success' => true,
                'message' => 'Informations du client mises à jour avec succès',
                'data' => new CompteResource($compte)
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'COMPTE_NOT_FOUND',
                    'message' => 'Le compte avec l\'ID spécifié n\'existe pas',
                    'details' => [
                        'compteId' => $compteId
                    ]
                ]
            ], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'Les données fournies sont invalides',
                    'details' => $e->errors()
                ]
            ], 400);
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la mise à jour des informations client: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => 'Erreur interne du serveur',
                    'details' => config('app.debug') ? $e->getMessage() : null
                ]
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/comptes/{compteId}",
     *     summary="Supprimer un compte (soft delete)",
     *     description="Supprime un compte en effectuant un soft delete. Le compte sera marqué comme fermé avec une date de fermeture.",
     *     tags={"Comptes"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="compteId",
     *         in="path",
     *         required=true,
     *         description="ID du compte à supprimer",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Compte supprimé avec succès",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Compte supprimé avec succès"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000"),
     *                 @OA\Property(property="numeroCompte", type="string", example="C00123456"),
     *                 @OA\Property(property="statut", type="string", example="ferme"),
     *                 @OA\Property(property="dateFermeture", type="string", format="date-time", example="2025-10-19T11:15:00Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Compte non trouvé",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(
     *                 property="error",
     *                 type="object",
     *                 @OA\Property(property="code", type="string", example="COMPTE_NOT_FOUND"),
     *                 @OA\Property(property="message", type="string", example="Le compte avec l'ID spécifié n'existe pas")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(
     *                 property="error",
     *                 type="object",
     *                 @OA\Property(property="code", type="string", example="INTERNAL_ERROR"),
     *                 @OA\Property(property="message", type="string", example="Erreur interne du serveur")
     *             )
     *         )
     *     )
     * )
     */
    public function destroy($compteId): JsonResponse
    {
        try {
            $compte = Compte::findOrFail($compteId);

            // Effectuer le soft delete
            $compte->delete();

            return response()->json([
                'success' => true,
                'message' => 'Compte supprimé avec succès',
                'data' => [
                    'id' => $compte->id,
                    'numeroCompte' => $compte->numero,
                    'statut' => 'ferme',
                    'dateFermeture' => $compte->deleted_at->toISOString()
                ]
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'COMPTE_NOT_FOUND',
                    'message' => 'Le compte avec l\'ID spécifié n\'existe pas',
                    'details' => [
                        'compteId' => $compteId
                    ]
                ]
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la suppression du compte: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => 'Erreur interne du serveur',
                    'details' => config('app.debug') ? $e->getMessage() : null
                ]
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/dieng/v1/comptes/archives/list",
     *     summary="Lister les comptes archivés",
     *     tags={"Comptes"},
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
     *     path="/api/v1/comptes/{compteId}/bloquer",
     *     summary="Bloquer un compte épargne",
     *     description="Bloque un compte épargne actif en spécifiant le motif et la durée de blocage. Calcule automatiquement la date de déblocage prévue.",
     *     tags={"Comptes"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="compteId",
     *         in="path",
     *         required=true,
     *         description="ID du compte à bloquer",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"motif", "duree", "unite"},
     *             @OA\Property(property="motif", type="string", description="Motif du blocage", example="Activité suspecte détectée"),
     *             @OA\Property(property="duree", type="integer", description="Durée du blocage", example=30),
     *             @OA\Property(property="unite", type="string", enum={"jours", "semaines", "mois"}, description="Unité de temps", example="mois")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Compte bloqué avec succès",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Compte bloqué avec succès"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000"),
     *                 @OA\Property(property="statut", type="string", example="bloque"),
     *                 @OA\Property(property="motifBlocage", type="string", example="Activité suspecte détectée"),
     *                 @OA\Property(property="dateBlocage", type="string", format="date-time", example="2025-10-19T11:20:00Z"),
     *                 @OA\Property(property="dateDeblocagePrevue", type="string", format="date-time", example="2025-11-18T11:20:00Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Erreur de validation ou compte non éligible au blocage",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(
     *                 property="error",
     *                 type="object",
     *                 @OA\Property(property="code", type="string", example="ACCOUNT_NOT_ELIGIBLE"),
     *                 @OA\Property(property="message", type="string", example="Seuls les comptes épargne actifs peuvent être bloqués")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Compte non trouvé",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(
     *                 property="error",
     *                 type="object",
     *                 @OA\Property(property="code", type="string", example="COMPTE_NOT_FOUND"),
     *                 @OA\Property(property="message", type="string", example="Le compte avec l'ID spécifié n'existe pas")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(
     *                 property="error",
     *                 type="object",
     *                 @OA\Property(property="code", type="string", example="INTERNAL_ERROR"),
     *                 @OA\Property(property="message", type="string", example="Erreur interne du serveur")
     *             )
     *         )
     *     )
     * )
     */
    public function bloquer(\App\Http\Requests\BlockAccountRequest $request, $compteId): JsonResponse
    {
        try {
            $compte = Compte::findOrFail($compteId);

            // Vérifier que c'est un compte épargne actif
            if ($compte->type !== 'epargne' || $compte->statut !== 'actif') {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'ACCOUNT_NOT_ELIGIBLE',
                        'message' => 'Seuls les comptes épargne actifs peuvent être bloqués',
                        'details' => [
                            'compteId' => $compteId,
                            'type' => $compte->type,
                            'statut' => $compte->statut
                        ]
                    ]
                ], 400);
            }

            $validated = $request->validated();

            // Calculer la date de déblocage prévue
            $dateBlocage = now();
            $dateDeblocagePrevue = $this->calculateUnblockDate($dateBlocage, $validated['duree'], $validated['unite']);

            // Mettre à jour le compte
            $compte->update([
                'statut' => 'bloque',
                'motif_blocage' => $validated['motif'],
                'date_blocage' => $dateBlocage,
                'date_deblocage_prevue' => $dateDeblocagePrevue,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Compte bloqué avec succès',
                'data' => [
                    'id' => $compte->id,
                    'statut' => $compte->statut,
                    'motifBlocage' => $compte->motif_blocage,
                    'dateBlocage' => $compte->date_blocage->toISOString(),
                    'dateDeblocagePrevue' => $compte->date_deblocage_prevue->toISOString()
                ]
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'COMPTE_NOT_FOUND',
                    'message' => 'Le compte avec l\'ID spécifié n\'existe pas',
                    'details' => [
                        'compteId' => $compteId
                    ]
                ]
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Erreur lors du blocage du compte: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => 'Erreur interne du serveur',
                    'details' => config('app.debug') ? $e->getMessage() : null
                ]
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/comptes/{compteId}/debloquer",
     *     summary="Débloquer un compte épargne",
     *     description="Débloque un compte épargne bloqué en spécifiant le motif de déblocage.",
     *     tags={"Comptes"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="compteId",
     *         in="path",
     *         required=true,
     *         description="ID du compte à débloquer",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"motif"},
     *             @OA\Property(property="motif", type="string", description="Motif du déblocage", example="Vérification complétée")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Compte débloqué avec succès",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Compte débloqué avec succès"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000"),
     *                 @OA\Property(property="statut", type="string", example="actif"),
     *                 @OA\Property(property="dateDeblocage", type="string", format="date-time", example="2025-10-19T12:00:00Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Erreur de validation ou compte non éligible au déblocage",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(
     *                 property="error",
     *                 type="object",
     *                 @OA\Property(property="code", type="string", example="ACCOUNT_NOT_ELIGIBLE"),
     *                 @OA\Property(property="message", type="string", example="Seuls les comptes épargne bloqués peuvent être débloqués")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Compte non trouvé",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(
     *                 property="error",
     *                 type="object",
     *                 @OA\Property(property="code", type="string", example="COMPTE_NOT_FOUND"),
     *                 @OA\Property(property="message", type="string", example="Le compte avec l'ID spécifié n'existe pas")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(
     *                 property="error",
     *                 type="object",
     *                 @OA\Property(property="code", type="string", example="INTERNAL_ERROR"),
     *                 @OA\Property(property="message", type="string", example="Erreur interne du serveur")
     *             )
     *         )
     *     )
     * )
     */
    public function debloquer(\App\Http\Requests\UnblockAccountRequest $request, $compteId): JsonResponse
    {
        try {
            $compte = Compte::findOrFail($compteId);

            // Vérifier que c'est un compte épargne bloqué
            if ($compte->type !== 'epargne' || $compte->statut !== 'bloque') {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'ACCOUNT_NOT_ELIGIBLE',
                        'message' => 'Seuls les comptes épargne bloqués peuvent être débloqués',
                        'details' => [
                            'compteId' => $compteId,
                            'type' => $compte->type,
                            'statut' => $compte->statut
                        ]
                    ]
                ], 400);
            }

            $validated = $request->validated();

            // Mettre à jour le compte
            $compte->update([
                'statut' => 'actif',
                'motif_deblocage' => $validated['motif'],
                'date_deblocage_prevue' => null, // Réinitialiser la date prévue
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Compte débloqué avec succès',
                'data' => [
                    'id' => $compte->id,
                    'statut' => $compte->statut,
                    'dateDeblocage' => now()->toISOString()
                ]
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'COMPTE_NOT_FOUND',
                    'message' => 'Le compte avec l\'ID spécifié n\'existe pas',
                    'details' => [
                        'compteId' => $compteId
                    ]
                ]
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Erreur lors du déblocage du compte: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => 'Erreur interne du serveur',
                    'details' => config('app.debug') ? $e->getMessage() : null
                ]
            ], 500);
        }
    }

    /**
     * Calculate unblock date based on duration and unit
     */
    private function calculateUnblockDate(\Carbon\Carbon $startDate, int $duration, string $unit): \Carbon\Carbon
    {
        return match ($unit) {
            'jours' => $startDate->copy()->addDays($duration),
            'semaines' => $startDate->copy()->addWeeks($duration),
            'mois' => $startDate->copy()->addMonths($duration),
            default => $startDate->copy()->addDays($duration)
        };
    }

    /**
     * @OA\Post(
     *     path="/api/v1/comptes/{id}/restore",
     *     summary="Restaurer un compte archivé",
     *     tags={"Comptes"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
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