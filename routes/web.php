<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('users', function () {
    return Inertia::render('users/index', [
        'users' => [], // Empty array for client-side loading
    ]);
})->middleware(['auth', 'verified'])->name('users.index');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
