<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\CompteController;
use App\Http\Controllers\Api\V1\ClientController;
use App\Http\Controllers\Api\V1\TransactionController;

// Routes API V1 avec préfixe dieng
Route::prefix('v1')->group(function () {

    // Route de test simple
    Route::get('test', function() {
        return response()->json(['success' => true, 'message' => 'API fonctionne']);
    });

    // Routes des clients (routes individuelles)
    Route::get('clients', [ClientController::class, 'index']);
    Route::post('clients', [ClientController::class, 'store']);
    Route::get('clients/{client}', [ClientController::class, 'show']);
    Route::put('clients/{client}', [ClientController::class, 'update']);
    Route::delete('clients/{client}', [ClientController::class, 'destroy']);

    // Routes des comptes (routes individuelles)
    Route::middleware('rating')->group(function () {
        Route::get('comptes', [CompteController::class, 'index']);
        Route::get('comptes/{compte}', [CompteController::class, 'show']);
        Route::get('comptes/archives/list', [CompteController::class, 'archives']);
    });
    Route::post('comptes', [CompteController::class, 'store']);
    Route::put('comptes/{compte}', [CompteController::class, 'update']);
    Route::delete('comptes/{compte}', [CompteController::class, 'destroy']);

    // Routes des transactions (routes individuelles)
    Route::get('transactions', [TransactionController::class, 'index']);
    Route::post('transactions', [TransactionController::class, 'store']);
    Route::get('transactions/{transaction}', [TransactionController::class, 'show']);
    Route::put('transactions/{transaction}', [TransactionController::class, 'update']);
    Route::delete('transactions/{transaction}', [TransactionController::class, 'destroy']);

    // Routes spéciales pour la gestion des comptes archivés
    Route::post('comptes/{id}/restore', [CompteController::class, 'restore']);
});
