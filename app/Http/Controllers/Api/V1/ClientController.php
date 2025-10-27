<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Validations\ClientValidation;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Schema(
 *     schema="Client",
 *     type="object",
 *     title="Client",
 *     description="Représente un client bancaire",
 *     @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000"),
 *     @OA\Property(property="nom", type="string", example="Martin"),
 *     @OA\Property(property="prenom", type="string", example="Jean"),
 *     @OA\Property(property="email", type="string", example="jean.martin@example.com"),
 *     @OA\Property(property="telephone", type="string", example="01 23 45 67 89"),
 *     @OA\Property(property="adresse", type="string", example="123 Avenue de la Test"),
 *     @OA\Property(property="statut", type="string", example="actif"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class ClientController extends Controller
{
    /**
     * @OA\Get(
     *     path="/dieng/v1/clients",
     *     summary="Lister tous les clients",
     *     tags={"Clients"},
     *     @OA\Response(
     *         response=200,
     *         description="Liste des clients",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Client"))
     *         )
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        $clients = Client::with('comptes')->get();

        return response()->json([
            'success' => true,
            'data' => $clients
        ]);
    }

    /**
     * @OA\Post(
     *     path="/dieng/v1/clients",
     *     summary="Créer un nouveau client",
     *     tags={"Clients"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"nom", "prenom", "email"},
     *             @OA\Property(property="nom", type="string"),
     *             @OA\Property(property="prenom", type="string"),
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="telephone", type="string"),
     *             @OA\Property(property="adresse", type="string"),
     *             @OA\Property(property="statut", type="string", enum={"actif", "inactif", "suspendu"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Client créé",
     *         @OA\JsonContent(ref="#/components/schemas/Client")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreurs de validation"
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        // Validation des données
        $errors = ClientValidation::validateCreate($request->all());
        if (!empty($errors)) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'Erreurs de validation',
                    'details' => $errors
                ]
            ], 422);
        }

        // Vérification unicité email
        if (!ClientValidation::isEmailUnique($request->email)) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'EMAIL_NOT_UNIQUE',
                    'message' => 'Cet email est déjà utilisé',
                    'details' => ['email' => 'L\'email doit être unique']
                ]
            ], 422);
        }

        // Création du client
        $client = Client::create($request->all());

        return response()->json([
            'success' => true,
            'data' => $client
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/dieng/v1/clients/{id}",
     *     summary="Obtenir un client spécifique",
     *     tags={"Clients"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Détails du client",
     *         @OA\JsonContent(ref="#/components/schemas/Client")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Client non trouvé"
     *     )
     * )
     */
    public function show(string $id): JsonResponse
    {
        $client = Client::with('comptes.transactions')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $client
        ]);
    }

    /**
     * @OA\Put(
     *     path="/dieng/v1/clients/{id}",
     *     summary="Mettre à jour un client",
     *     tags={"Clients"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="nom", type="string"),
     *             @OA\Property(property="prenom", type="string"),
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="telephone", type="string"),
     *             @OA\Property(property="adresse", type="string"),
     *             @OA\Property(property="statut", type="string", enum={"actif", "inactif", "suspendu"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Client mis à jour",
     *         @OA\JsonContent(ref="#/components/schemas/Client")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreurs de validation"
     *     )
     * )
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $client = Client::findOrFail($id);

        // Validation des données
        $errors = ClientValidation::validateUpdate($request->all());
        if (!empty($errors)) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'Erreurs de validation',
                    'details' => $errors
                ]
            ], 422);
        }

        // Vérification unicité email si fourni
        if ($request->has('email') && !ClientValidation::isEmailUnique($request->email, $id)) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'EMAIL_NOT_UNIQUE',
                    'message' => 'Cet email est déjà utilisé',
                    'details' => ['email' => 'L\'email doit être unique']
                ]
            ], 422);
        }

        // Mise à jour du client
        $client->update($request->all());

        return response()->json([
            'success' => true,
            'data' => $client
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/dieng/v1/clients/{id}",
     *     summary="Supprimer un client",
     *     tags={"Clients"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Client supprimé"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Client non trouvé"
     *     )
     * )
     */
    public function destroy(string $id): JsonResponse
    {
        $client = Client::findOrFail($id);
        $client->delete();

        return response()->json(null, 204);
    }
}
