<?php

use App\Http\Controllers\Admin\V1\AdminController;
use App\Http\Controllers\Admin\V1\ArtistController;
use App\Http\Controllers\Admin\V1\RoleController;
use App\Http\Controllers\Admin\V1\UploadController;
use App\Http\Controllers\Admin\V1\UserController;
use Illuminate\Support\Facades\Route;

// V1 API Routes - 60 requests per minute
Route::prefix('admin/v1')->middleware(['web', 'auth:admin,api', 'throttle:60,1'])->group(function () {
    // Role Controller
    Route::get('roles', [RoleController::class, 'index'])->name('admin.v1.roles.index');

    // Upload Controller
    Route::post('upload/temp', [UploadController::class, 'storeTemp'])->name('admin.v1.upload.temp');

    // Admin Controller
    Route::apiResource('admins', AdminController::class)->names('admin.v1.admins');

    // Custom admin routes
    Route::post('admins/{admin}/restore', [AdminController::class, 'restore'])
        ->name('admin.v1.admins.restore')
        ->withTrashed();
    Route::delete('admins/{admin}/force-delete', [AdminController::class, 'forceDelete'])
        ->name('admin.v1.admins.force-delete')
        ->withTrashed();
    Route::delete('admins/{admin}/profile-photo', [AdminController::class, 'removeProfilePhoto'])
        ->name('admin.v1.admins.profile-photo.delete');
    Route::post('admins/{admin}/send-password-reset', [AdminController::class, 'sendPasswordResetLink'])
        ->name('admin.v1.admins.send-password-reset');

    // User Controller
    Route::apiResource('users', UserController::class)->names('admin.v1.users');

    // Custom user routes
    Route::post('users/{user}/restore', [UserController::class, 'restore'])
        ->name('admin.v1.users.restore')
        ->withTrashed();
    Route::delete('users/{user}/force-delete', [UserController::class, 'forceDelete'])
        ->name('admin.v1.users.force-delete')
        ->withTrashed();
    Route::delete('users/{user}/profile-photo', [UserController::class, 'removeProfilePhoto'])
        ->name('admin.v1.users.profile-photo.delete');
    Route::post('users/{user}/send-password-reset', [UserController::class, 'sendPasswordResetLink'])
        ->name('admin.v1.users.send-password-reset');

    // Artist Controller
    Route::apiResource('artists', ArtistController::class)->names('admin.v1.artists');

    // Custom artist routes
    Route::post('artists/{artist}/restore', [ArtistController::class, 'restore'])
        ->name('admin.v1.artists.restore')
        ->withTrashed();
    Route::delete('artists/{artist}/force-delete', [ArtistController::class, 'forceDelete'])
        ->name('admin.v1.artists.force-delete')
        ->withTrashed();
    Route::delete('artists/{artist}/profile-photo', [ArtistController::class, 'removeProfilePhoto'])
        ->name('admin.v1.artists.profile-photo.delete');
});
