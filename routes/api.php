<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\JeepController;
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\RouteController;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Jeeps
    Route::apiResource('jeeps', JeepController::class);

    // Location tracking
    Route::post('/jeeps/{jeep}/location', [LocationController::class, 'update']);
    Route::get('/jeeps/{jeep}/location', [LocationController::class, 'current']);
    Route::get('/jeeps/{jeep}/location/history', [LocationController::class, 'history']);

    // Routes / trips
    Route::apiResource('jeeps.trips', RouteController::class)->shallow();
});
