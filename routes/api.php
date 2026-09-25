<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\GbpContentController;

// API routes (for future mobile app / integrations) go here.
Route::get('/ping', fn() => ['ok' => true]);

Route::post('/login', [AuthController::class, 'login']);

// ---- Mobile app — Core module (token-authenticated) ----
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::get('/dashboard', [DashboardController::class, 'index']);

    Route::get('/reviews', [ReviewController::class, 'index']);
    Route::get('/reviews/{location}', [ReviewController::class, 'show']);
    Route::post('/reviews/{review}/reply', [ReviewController::class, 'reply']);

    Route::get('/gbp-content', [GbpContentController::class, 'index']);

    // AI reply requires an active plan — same 402 JSON contract as the web app.
    Route::middleware('plan')->group(function () {
        Route::post('/reviews/{review}/generate', [ReviewController::class, 'generateReply']);
    });
});

// API routes (for future mobile app / integrations) go here.
Route::get('/ping', fn () => ['ok' => true]);
