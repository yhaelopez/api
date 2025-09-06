<?php

use App\Http\Controllers\Api\V1\ArtistController;
use App\Http\Controllers\Api\V1\RoleController;
use App\Http\Controllers\Api\V1\UploadController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;

// V1 API Routes - 60 requests per minute
Route::prefix('v1')->middleware(['web', 'auth:admin,api', 'throttle:60,1'])->group(function () {
    // Role Controller
    Route::get('roles', [RoleController::class, 'index'])->name('v1.roles.index');

    // Upload Controller
    Route::post('upload/temp', [UploadController::class, 'storeTemp'])->name('v1.upload.temp');

    // User Controller
    Route::apiResource('users', UserController::class)->names('v1.users');

    // Custom user routes
    Route::post('users/{user}/restore', [UserController::class, 'restore'])
        ->name('v1.users.restore')
        ->withTrashed();
    Route::delete('users/{user}/force-delete', [UserController::class, 'forceDelete'])
        ->name('v1.users.force-delete')
        ->withTrashed();
    Route::delete('users/{user}/profile-photo', [UserController::class, 'removeProfilePhoto'])
        ->name('v1.users.profile-photo.delete');
    Route::post('users/{user}/send-password-reset', [UserController::class, 'sendPasswordResetLink'])
        ->name('v1.users.send-password-reset');

    // Artist Controller
    Route::apiResource('artists', ArtistController::class)->names('v1.artists');

    // Custom artist routes
    Route::post('artists/{artist}/restore', [ArtistController::class, 'restore'])
        ->name('v1.artists.restore')
        ->withTrashed();
    Route::delete('artists/{artist}/force-delete', [ArtistController::class, 'forceDelete'])
        ->name('v1.artists.force-delete')
        ->withTrashed();
    Route::delete('artists/{artist}/profile-photo', [ArtistController::class, 'removeProfilePhoto'])
        ->name('v1.artists.profile-photo.delete');
});
