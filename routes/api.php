<?php

use Illuminate\Support\Facades\Route;

// API routes (for future mobile app / integrations) go here.
Route::get('/ping', fn () => ['ok' => true]);
