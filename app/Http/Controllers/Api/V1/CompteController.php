<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * @OA\Schema(
 *     schema="Compte",
 *     type="object",
 *     title="Compte",
 *     description="Représente un compte bancaire",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="numero", type="string", example="1234567890"),
 *     @OA\Property(property="type", type="string", example="epargne"),
 *     @OA\Property(property="statut", type="string", example="actif"),
 *     @OA\Property(property="solde", type="number", format="float", example=1500.50)
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
        // Réponse temporaire pour Swagger
        return response()->json([
            'success' => true,
            'data' => [
                [
                    'id' => 1,
                    'numero' => '1234567890',
                    'type' => 'epargne',
                    'statut' => 'actif',
                    'solde' => 1500.50
                ],
                [
                    'id' => 2,
                    'numero' => '0987654321',
                    'type' => 'cheque',
                    'statut' => 'bloque',
                    'solde' => 250.00
                ]
            ]
        ]);
    }
}
