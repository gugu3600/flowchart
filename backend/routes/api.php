<?php

use App\Http\Controllers\api\AdminController;
use App\Http\Controllers\api\AuthController;
use App\Http\Controllers\api\FlowController;
use App\Http\Controllers\api\TableDefinitionController;
use App\Http\Controllers\api\LogicDefinitionController;
use Illuminate\Support\Facades\Route;

// ── public routes ──────────────────────────────────────────────────
Route::post('register', [AuthController::class, 'register'])->middleware('throttle:login');
Route::post('login', [AuthController::class, 'login'])->middleware('throttle:login');
Route::get('payment-methods', [AuthController::class, 'paymentMethods']);

// ── authenticated routes ───────────────────────────────────────────
Route::middleware('auth:api')->group(function () {
    // auth
    Route::get('me', [AuthController::class, 'me']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('subscribe', [AuthController::class, 'subscribe']);

    // stats (available to all authenticated users)
    Route::get('stats', [AdminController::class, 'stats']);

    // flows — all authenticated can read, but write requires save-flows permission
    Route::get('flows', [FlowController::class, 'index']);
    Route::get('flows/{flow}', [FlowController::class, 'show']);
    Route::middleware('permission:save-flows')->group(function () {
        Route::post('flows', [FlowController::class, 'store']);
        Route::put('flows/{flow}', [FlowController::class, 'update']);
        Route::delete('flows/{flow}', [FlowController::class, 'destroy']);
        Route::post('flows/{flow}/nodes', [FlowController::class, 'saveNodes']);
        Route::post('flows/{flow}/edges', [FlowController::class, 'saveEdges']);
        Route::post('flows/{flow}/save', [FlowController::class, 'save']);
    });

    // table definitions — write requires generate-schema permission
    Route::get('tables', [TableDefinitionController::class, 'index']);
    Route::get('tables/{table}', [TableDefinitionController::class, 'show']);
    Route::middleware('permission:generate-schema')->group(function () {
        Route::post('tables', [TableDefinitionController::class, 'store']);
        Route::put('tables/{table}', [TableDefinitionController::class, 'update']);
        Route::delete('tables/{table}', [TableDefinitionController::class, 'destroy']);
    });

    // logic definitions — all authenticated users can CRUD (free tier limited by service)
    Route::get('logics', [LogicDefinitionController::class, 'index']);
    Route::get('logics/{logic}', [LogicDefinitionController::class, 'show']);
    Route::post('logics', [LogicDefinitionController::class, 'store']);
    Route::put('logics/{logic}', [LogicDefinitionController::class, 'update']);
    Route::delete('logics/{logic}', [LogicDefinitionController::class, 'destroy']);

    // admin — super-admin only
    Route::middleware('role:super-admin')->prefix('admin')->group(function () {
        Route::get('tiers', [AdminController::class, 'tiers']);
        Route::get('users', [AdminController::class, 'users']);
        Route::get('users/{user}', [AdminController::class, 'show']);
        Route::put('users/{user}', [AdminController::class, 'update']);
        Route::put('users/{user}/roles', [AdminController::class, 'updateRoles']);
        Route::put('users/{user}/upgrade', [AdminController::class, 'upgrade']);
        Route::delete('users/{user}', [AdminController::class, 'destroy']);
    });
});
