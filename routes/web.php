<?php

use App\Http\Controllers\Dashboard\HomeController;
use App\Http\Controllers\Dashboard\RoleController;
use App\Http\Controllers\Dashboard\UserController;
use App\Http\Controllers\Dashboard\ServiceController;
use App\Http\Controllers\ProfileController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth'])->group(function () {


    Route::prefix('dashboard')->name('dashboard.')->group(function () {

        Route::get('/', [HomeController::class, 'index'])->name('index');

        Route::resource('users', UserController::class);

        Route::resource('roles', RoleController::class);
        Route::get('roles/create', [RoleController::class, 'create'])->name('roles.create');
        Route::post('roles', [RoleController::class, 'store'])->name('roles.store');

        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

        // services
        Route::resource('services', ServiceController::class);

    });

});

require __DIR__ . '/auth.php';
