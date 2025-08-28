<?php

use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

// Group the routes under the web middleware to handle sessions
Route::middleware(['web', 'auth', 'throttle:60,1'])->group(function () {
    // User Controller - 60 requests per minute
    Route::apiResource('users', UserController::class);
});
