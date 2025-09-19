<?php

use App\Http\Controllers\RoleController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::group(['middleware' => ['auth']], function () {
    Route::get('roles/permissions', [RoleController::class, 'permissions'])->name('roles.permissions');
    Route::resource('roles', RoleController::class);
    Route::resource('users', \App\Http\Controllers\UserController::class)->except('show');
});



require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';