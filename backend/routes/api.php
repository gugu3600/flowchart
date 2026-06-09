<?php

use App\Http\Controllers\api\AuthController;
use App\Http\Controllers\api\FlowController;
use App\Http\Controllers\api\TableDefinitionController;
use App\Http\Controllers\api\LogicDefinitionController;
use Illuminate\Support\Facades\Route;

// ── public routes ──────────────────────────────────────────────────
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

// ── authenticated routes ───────────────────────────────────────────
Route::middleware('auth:api')->group(function () {
    // auth
    Route::get('me', [AuthController::class, 'me']);
    Route::post('logout', [AuthController::class, 'logout']);

    // flows
    Route::apiResource('flows', FlowController::class);
    Route::post('flows/{flow}/nodes', [FlowController::class, 'saveNodes']);
    Route::post('flows/{flow}/edges', [FlowController::class, 'saveEdges']);
    Route::post('flows/{flow}/save', [FlowController::class, 'save']);

    // table definitions
    Route::apiResource('tables', TableDefinitionController::class);

    // logic definitions
    Route::apiResource('logics', LogicDefinitionController::class);
});
