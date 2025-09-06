<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Home route
Route::get('/', function () {
    return redirect()->route('login');
})->name('home');

// Note: Broadcasting authentication not needed for public channels

Route::get('/_dev/sms', function (App\Services\TwilioSms $sms) {
    return response()->json($sms->send('+15551234567', 'Hola desde Twilio Mock'));
});

// Include auth routes (now using admin guard)
require __DIR__.'/auth.php';

// Include settings routes
require __DIR__.'/settings.php';

// Admin management routes
Route::middleware('auth:admin')->group(function () {
    Route::get('users', function () {
        return inertia('users/index');
    })->name('users.index');
    
    Route::get('artists', function () {
        return inertia('artists/index');
    })->name('artists.index');
});
