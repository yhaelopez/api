<?php

use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

// Group the routes under the sanctum middleware
Route::middleware('auth:sanctum')->group(function () {
    // User Controller
    Route::apiResource('users', UserController::class);
});
