<?php

use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;

// V1 API Routes
Route::prefix('v1')->middleware(['web', 'auth', 'throttle:60,1'])->group(function () {
    // User Controller - 60 requests per minute
    Route::apiResource('users', UserController::class);

    // Custom user routes
    Route::post('users/{user}/restore', [UserController::class, 'restore'])
        ->name('users.restore')
        ->withTrashed();
    Route::delete('users/{user}/force-delete', [UserController::class, 'forceDelete'])
        ->name('users.force-delete')
        ->withTrashed();
});
