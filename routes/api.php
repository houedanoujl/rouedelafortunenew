<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PrizeApiController;

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

// Route par défaut pour obtenir les informations de l'utilisateur authentifié
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Routes pour l'API de synchronisation des images
Route::middleware('auth:sanctum')->group(function () {
    // Routes pour les prix
    Route::get('/prizes', [PrizeApiController::class, 'index']);
    Route::get('/prizes/{id}', [PrizeApiController::class, 'show']);
    Route::post('/prizes/{id}/upload-image', [PrizeApiController::class, 'uploadImage']);
});

Route::post('/no-stock-wheel-angle', function(Request $request) {
    // Même logique que dans SpinResultController pour la roue no-stock
    $segments = [
        ['text' => 'PERDU'], ['text' => 'GAGNÉ'], ['text' => 'PERDU'], ['text' => 'GAGNÉ'], ['text' => 'PERDU'],
        ['text' => 'GAGNÉ'], ['text' => 'PERDU'], ['text' => 'GAGNÉ'], ['text' => 'PERDU'], ['text' => 'GAGNÉ']
    ];
    $targetIndexes = [0,2,4,6,8]; // Toujours un secteur perdant
    $chosenIndex = $targetIndexes[0]; // Ou random côté backend si souhaité
    $target_angle = ($chosenIndex * 36) + 18;
    return response()->json(['target_angle' => $target_angle]);
});
