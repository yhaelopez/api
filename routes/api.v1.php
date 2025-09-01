<?php

use App\Http\Controllers\Api\V1\RoleController;
use App\Http\Controllers\Api\V1\UploadController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;

// V1 API Routes
Route::prefix('v1')->middleware(['web', 'auth', 'throttle:60,1'])->group(function () {
    // User Controller - 60 requests per minute
    Route::apiResource('users', UserController::class);

    // Role Controller - 60 requests per minute
    Route::get('roles', [RoleController::class, 'index'])->name('roles.index');

    // Upload Controller - Temporary file uploads
    Route::post('upload/temp', [UploadController::class, 'storeTemp'])->name('upload.temp');

    // Custom user routes
    Route::post('users/{user}/restore', [UserController::class, 'restore'])
        ->name('users.restore')
        ->withTrashed();
    Route::delete('users/{user}/force-delete', [UserController::class, 'forceDelete'])
        ->name('users.force-delete')
        ->withTrashed();
    Route::delete('users/{user}/profile-photo', [UserController::class, 'removeProfilePhoto'])
        ->name('users.profile-photo.delete');
});
