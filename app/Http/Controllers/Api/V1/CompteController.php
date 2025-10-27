<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Compte;
use Illuminate\Http\Request;

/**
 * @OA\Schema(
 *     schema="Compte",
 *     type="object",
 *     title="Compte",
 *     description="Représente un compte bancaire",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="numero", type="string", example="CMPT2025A1B2C3D4"),
 *     @OA\Property(property="type", type="string", example="epargne"),
 *     @OA\Property(property="statut", type="string", example="actif"),
 *     @OA\Property(property="client_id", type="integer", example=1),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class CompteController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/comptes",
     *     summary="Lister tous les comptes",
     *     tags={"Comptes"},
     *     @OA\Response(
     *         response=200,
     *         description="Liste des comptes",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Compte"))
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $comptes = Compte::with('client')->get();

        return response()->json([
            'success' => true,
            'data' => $comptes
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/comptes/{id}",
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
    public function show($id)
    {
        $compte = Compte::with(['client', 'transactions'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $compte
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/comptes",
     *     summary="Créer un nouveau compte",
     *     tags={"Comptes"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"type", "client_id"},
     *             @OA\Property(property="type", type="string", enum={"epargne", "cheque", "courant"}),
     *             @OA\Property(property="statut", type="string", enum={"actif", "bloque", "ferme"}),
     *             @OA\Property(property="client_id", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Compte créé",
     *         @OA\JsonContent(ref="#/components/schemas/Compte")
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:epargne,cheque,courant',
            'statut' => 'in:actif,bloque,ferme',
            'client_id' => 'required|exists:clients,id'
        ]);

        $compte = Compte::create($validated);
        $compte->load('client');

        return response()->json([
            'success' => true,
            'data' => $compte
        ], 201);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/comptes/{id}",
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
     *             type="object",
     *             @OA\Property(property="type", type="string", enum={"epargne", "cheque", "courant"}),
     *             @OA\Property(property="statut", type="string", enum={"actif", "bloque", "ferme"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Compte mis à jour",
     *         @OA\JsonContent(ref="#/components/schemas/Compte")
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        $compte = Compte::findOrFail($id);

        $validated = $request->validate([
            'type' => 'sometimes|in:epargne,cheque,courant',
            'statut' => 'sometimes|in:actif,bloque,ferme'
        ]);

        $compte->update($validated);
        $compte->load('client');

        return response()->json([
            'success' => true,
            'data' => $compte
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/comptes/{id}",
     *     summary="Supprimer un compte",
     *     tags={"Comptes"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Compte supprimé"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Compte non trouvé"
     *     )
     * )
     */
    public function destroy($id)
    {
        $compte = Compte::findOrFail($id);
        $compte->delete();

        return response()->json(null, 204);
    }
}
