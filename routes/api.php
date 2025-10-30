<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\CompteController;
use App\Http\Controllers\AuthController;
use App\Http\Middleware\LoggingMiddleware;
use App\Http\Middleware\AuthMiddleware;
use App\Http\Middleware\RoleMiddleware;

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

// Authentication routes (public)
Route::prefix('v1/auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('refresh', [AuthController::class, 'refresh'])->middleware(AuthMiddleware::class);
    Route::post('logout', [AuthController::class, 'logout'])->middleware(AuthMiddleware::class);
    Route::get('me', [AuthController::class, 'me'])->middleware(AuthMiddleware::class);
});

// Protected API routes
Route::middleware(['api', LoggingMiddleware::class])->group(function () {
    Route::prefix('v1')->group(function () {
        // Routes pour les comptes (Admin only)
        // Route::middleware(RoleMiddleware::class . ':admin')->group(function () {
            Route::get('comptes', [CompteController::class, 'index'])->name('api.comptes.index');
            Route::post('comptes', [CompteController::class, 'store'])->name('api.comptes.store');
            Route::get('comptes/{compte}', [CompteController::class, 'show'])->name('api.comptes.show');
            Route::delete('comptes/{compte}', [CompteController::class, 'destroy'])->name('api.comptes.destroy');

            // Route PATCH pour mettre à jour les informations client
            Route::patch('comptes/{compte}', [CompteController::class, 'update'])->name('comptes.update.client');

            // Routes pour bloquer/débloquer un compte
            Route::post('comptes/{compte}/bloquer', [CompteController::class, 'bloquer'])->name('comptes.bloquer');
            Route::post('comptes/{compte}/debloquer', [CompteController::class, 'debloquer'])->name('comptes.debloquer');

            // Route pour restaurer un compte archivé
            Route::post('comptes/{compte}/restore', [CompteController::class, 'restore'])->name('comptes.restore');
        // });
    });
});

// Disable auto-generated API routes to prevent conflicts
// Only use manually defined routes above

