<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

use App\Http\Controllers\Api\V1\CompteController;
use App\Http\Controllers\Api\V1\ClientController;
use App\Http\Controllers\Api\V1\TransactionController;

// Routes API V1
Route::prefix('v1')->group(function () {
    // Routes des comptes
    Route::apiResource('comptes', CompteController::class);

    // Routes des clients
    Route::apiResource('clients', ClientController::class);

    // Routes des transactions
    Route::apiResource('transactions', TransactionController::class);
});
