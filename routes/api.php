<?php

use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

// Group the routes under the web middleware
Route::middleware('auth:web')->group(function () {
    // User Controller
    Route::apiResource('users', UserController::class);
});
