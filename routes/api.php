<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MobileModelController;

Route::prefix('v1')->group(function () {

    // ── 🔓 Public endpoints ───────────────────────────────────────
    Route::post('auth/login', [AuthController::class, 'login']);

    // ── 🔐 Protected endpoints (require Bearer token) ─────────────
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('users', [MobileModelController::class, 'users']);
        Route::get('course', [MobileModelController::class, 'course']);
        Route::get('modules', [MobileModelController::class, 'modules']);
        Route::get('activities', [MobileModelController::class, 'activities']);
        
        // add more protected routes here, e.g.
        // Route::post('modules/{module}/complete', …);
    });
});
