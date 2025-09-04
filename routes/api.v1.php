<?php

use App\Http\Controllers\Api\V1\ArtistController;
use App\Http\Controllers\Api\V1\RoleController;
use App\Http\Controllers\Api\V1\UploadController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;

// V1 API Routes - 60 requests per minute
Route::prefix('v1')->middleware(['web', 'auth', 'throttle:60,1'])->group(function () {
    // Role Controller
    Route::get('roles', [RoleController::class, 'index'])->name('roles.index');

    // Upload Controller
    Route::post('upload/temp', [UploadController::class, 'storeTemp'])->name('upload.temp');

    // User Controller
    Route::apiResource('users', UserController::class);

    // Custom user routes
    Route::post('users/{user}/restore', [UserController::class, 'restore'])
        ->name('users.restore')
        ->withTrashed();
    Route::delete('users/{user}/force-delete', [UserController::class, 'forceDelete'])
        ->name('users.force-delete')
        ->withTrashed();
    Route::delete('users/{user}/profile-photo', [UserController::class, 'removeProfilePhoto'])
        ->name('users.profile-photo.delete');
    Route::post('users/{user}/send-password-reset', [UserController::class, 'sendPasswordResetLink'])
        ->name('users.send-password-reset');

    // Artist Controller
    Route::apiResource('artists', ArtistController::class);

    // Custom artist routes
    Route::post('artists/{artist}/restore', [ArtistController::class, 'restore'])
        ->name('artists.restore')
        ->withTrashed();
    Route::delete('artists/{artist}/force-delete', [ArtistController::class, 'forceDelete'])
        ->name('artists.force-delete')
        ->withTrashed();
    Route::delete('artists/{artist}/profile-photo', [ArtistController::class, 'removeProfilePhoto'])
        ->name('artists.profile-photo.delete');
});
