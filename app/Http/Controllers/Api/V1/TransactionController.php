<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;

/**
 * @OA\Schema(
 *     schema="Transaction",
 *     type="object",
 *     title="Transaction",
 *     description="Représente une transaction bancaire",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="reference", type="string", example="TRX20251026001"),
 *     @OA\Property(property="compte_id", type="integer", example=1),
 *     @OA\Property(property="type", type="string", example="depot"),
 *     @OA\Property(property="montant", type="number", format="float", example=1000.50),
 *     @OA\Property(property="description", type="string", example="Dépôt salaire"),
 *     @OA\Property(property="beneficiaire", type="string", example="Jean Martin"),
 *     @OA\Property(property="date_transaction", type="string", format="date-time"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class TransactionController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/transactions",
     *     summary="Lister toutes les transactions",
     *     tags={"Transactions"},
     *     @OA\Response(
     *         response=200,
     *         description="Liste des transactions",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Transaction"))
     *         )
     *     )
     * )
     */
    public function index()
    {
        $transactions = Transaction::with('compte.client')->get();

        return response()->json([
            'success' => true,
            'data' => $transactions
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/transactions",
     *     summary="Créer une nouvelle transaction",
     *     tags={"Transactions"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"type", "montant", "compte_id"},
     *             @OA\Property(property="type", type="string", enum={"depot", "retrait", "virement", "paiement"}),
     *             @OA\Property(property="montant", type="number", format="float", minimum=0.01),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="beneficiaire", type="string"),
     *             @OA\Property(property="date_transaction", type="string", format="date-time"),
     *             @OA\Property(property="compte_id", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Transaction créée",
     *         @OA\JsonContent(ref="#/components/schemas/Transaction")
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:depot,retrait,virement,paiement',
            'montant' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:255',
            'beneficiaire' => 'nullable|string|max:100',
            'date_transaction' => 'nullable|date',
            'compte_id' => 'required|exists:comptes,id'
        ]);

        // Utiliser la date actuelle si non fournie
        if (!isset($validated['date_transaction'])) {
            $validated['date_transaction'] = now();
        }

        $transaction = Transaction::create($validated);
        $transaction->load('compte.client');

        return response()->json([
            'success' => true,
            'data' => $transaction
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/transactions/{id}",
     *     summary="Obtenir une transaction spécifique",
     *     tags={"Transactions"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Détails de la transaction",
     *         @OA\JsonContent(ref="#/components/schemas/Transaction")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Transaction non trouvée"
     *     )
     * )
     */
    public function show($id)
    {
        $transaction = Transaction::with('compte.client')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $transaction
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/transactions/{id}",
     *     summary="Mettre à jour une transaction",
     *     tags={"Transactions"},
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
     *             @OA\Property(property="type", type="string", enum={"depot", "retrait", "virement", "paiement"}),
     *             @OA\Property(property="montant", type="number", format="float", minimum=0.01),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="beneficiaire", type="string"),
     *             @OA\Property(property="date_transaction", type="string", format="date-time")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Transaction mise à jour",
     *         @OA\JsonContent(ref="#/components/schemas/Transaction")
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        $transaction = Transaction::findOrFail($id);

        $validated = $request->validate([
            'type' => 'sometimes|in:depot,retrait,virement,paiement',
            'montant' => 'sometimes|numeric|min:0.01',
            'description' => 'nullable|string|max:255',
            'beneficiaire' => 'nullable|string|max:100',
            'date_transaction' => 'sometimes|date'
        ]);

        $transaction->update($validated);
        $transaction->load('compte.client');

        return response()->json([
            'success' => true,
            'data' => $transaction
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/transactions/{id}",
     *     summary="Supprimer une transaction",
     *     tags={"Transactions"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Transaction supprimée"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Transaction non trouvée"
     *     )
     * )
     */
    public function destroy($id)
    {
        $transaction = Transaction::findOrFail($id);
        $transaction->delete();

        return response()->json(null, 204);
    }
}
