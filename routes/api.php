<?php

use App\Enums\TokenAbility;

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


/*
|--------------------------------------------------------------------------
| Mobile App API Routes (Workers & Drivers)
|--------------------------------------------------------------------------
*/

Route::prefix('mobile')->group(function () {

    // Public: Authentication
    Route::post('login', [App\Http\Controllers\Api\AuthController::class, 'login']);

    // Protected Routes
    Route::middleware(['auth:sanctum'])->group(function () {

        // Common Auth Routes
        Route::post('logout', [App\Http\Controllers\Api\AuthController::class, 'logout']);
        Route::get('profile', [App\Http\Controllers\Api\AuthController::class, 'profile']);
        Route::get('profile/refresh', [App\Http\Controllers\Api\AuthController::class, 'refreshProfile']);

        // Driver-specific routes
        Route::prefix('driver')->middleware(['apiCategory:driver'])->group(function () {
            Route::get('dashboard', [App\Http\Controllers\Api\Driver\DriverController::class, 'dashboard']);

            // Fuel Requests
            Route::post('request', [App\Http\Controllers\Api\Driver\FuelRequestController::class, 'store']);
            Route::get('request/active', [App\Http\Controllers\Api\Driver\FuelRequestController::class, 'active']);
            Route::post('request/{id}/cancel', [App\Http\Controllers\Api\Driver\FuelRequestController::class, 'cancel']);
            Route::get('request/history', [App\Http\Controllers\Api\Driver\FuelRequestController::class, 'history']);

            // Nearby Stations
            Route::get('nearby-stations', [App\Http\Controllers\Api\Driver\StationController::class, 'nearbyStations']);
            Route::get('stations/{id}', [App\Http\Controllers\Api\Driver\StationController::class, 'show']);
            Route::get('fuel-types', [App\Http\Controllers\Api\Driver\StationController::class, 'fuelTypes']);
        });

        // Worker-specific routes
        Route::prefix('worker')->middleware(['apiCategory:worker'])->group(function () {
            Route::get('dashboard', [App\Http\Controllers\Api\Worker\WorkerController::class, 'dashboard']);

            // Transaction Verification & Execution
            Route::post('verify-request', [App\Http\Controllers\Api\Worker\TransactionController::class, 'verify']);
            Route::post('confirm-fueling', [App\Http\Controllers\Api\Worker\TransactionController::class, 'confirm']);
            Route::post('upload-proof', [App\Http\Controllers\Api\Worker\TransactionController::class, 'uploadProof']);

            // Stats & History
            Route::get('today-stats', [App\Http\Controllers\Api\Worker\TransactionController::class, 'todayStats']);
            Route::get('recent-transactions', [App\Http\Controllers\Api\Worker\TransactionController::class, 'recentTransactions']);
        });

    });

});

