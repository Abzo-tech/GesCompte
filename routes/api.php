<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\CompteController;
use App\Http\Middleware\LoggingMiddleware;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware(['api', LoggingMiddleware::class])->group(function () {
    Route::prefix('dieng/v1')->group(function () {
        // Routes pour les comptes
        Route::apiResource('comptes', CompteController::class)->except(['update']);

        // Route PATCH pour mettre à jour les informations client
        Route::patch('comptes/{compte}', [CompteController::class, 'update'])->name('comptes.update.client');

        // Routes pour bloquer/débloquer un compte
        Route::post('comptes/{compte}/bloquer', [CompteController::class, 'bloquer'])->name('comptes.bloquer');
        Route::post('comptes/{compte}/debloquer', [CompteController::class, 'debloquer'])->name('comptes.debloquer');

        // Route pour restaurer un compte archivé
        Route::post('comptes/{compte}/restore', [CompteController::class, 'restore'])->name('comptes.restore');
    });
});

