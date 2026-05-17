<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\JeepController;
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\RouteController;
use App\Http\Controllers\Api\JeepneyRouteController;
use App\Http\Controllers\Api\BookmarkController;
use App\Http\Controllers\Api\DriversController;
use App\Http\Controllers\Api\SightingController;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/driver-access', [DriversController::class, 'access']);

// Jeepney routes are readable without auth (guests can browse)
Route::get('/jeepney-routes', [JeepneyRouteController::class, 'index']);
Route::get('/jeepney-routes/{jeepneyRoute}', [JeepneyRouteController::class, 'show']);
Route::get('/active-jeeps', [JeepneyRouteController::class, 'activeJeeps']);

// Sightings — public read, public write (no auth required for crowdsourcing)
Route::get('/sightings', [SightingController::class, 'index']);
Route::post('/sightings', [SightingController::class, 'store']);
Route::patch('/sightings/{sighting}/confirm', [SightingController::class, 'confirm']);
Route::patch('/sightings/{sighting}/deny', [SightingController::class, 'deny']);

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

    // Trips
    Route::get('/trips', [RouteController::class, 'driverTrips']);
    Route::apiResource('jeeps.trips', RouteController::class)->shallow();

    // Bookmarks
    Route::get('/user/saved-routes', [BookmarkController::class, 'index']);
    Route::post('/user/saved-routes', [BookmarkController::class, 'store']);
    Route::delete('/user/saved-routes/{jeepneyRouteId}', [BookmarkController::class, 'destroy']);

    // Driver management (admin)
    Route::get('/drivers', [DriversController::class, 'index']);
    Route::post('/drivers/{user}/token', [DriversController::class, 'generateToken']);
});
