<?php

use App\Http\Controllers\Dashboard\EmployeeController;
use App\Http\Controllers\Dashboard\HomeController;
use App\Http\Controllers\Dashboard\RoleController;
use App\Http\Controllers\Dashboard\UserController;
use App\Http\Controllers\Dashboard\ServiceController;
use App\Http\Controllers\Dashboard\ProductController;
use App\Http\Controllers\Dashboard\PackageController;
use App\Http\Controllers\Dashboard\PackageSubscriptionController;
use App\Http\Controllers\Dashboard\PointController;
use App\Http\Controllers\Dashboard\WalletController;
use App\Http\Controllers\ProfileController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth'])->group(function () {


    Route::prefix('dashboard')->name('dashboard.')->group(function () {

        Route::get('/', [HomeController::class, 'index'])->name('index');

        Route::get('users/select2', [UserController::class, 'select2'])->name('users.select2');
        Route::resource('users', UserController::class);

        Route::resource('roles', RoleController::class);
        Route::get('roles/create', [RoleController::class, 'create'])->name('roles.create');
        Route::post('roles', [RoleController::class, 'store'])->name('roles.store');

        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');


        Route::get('services/{service}/sales-lines', [ServiceController::class, 'salesLinesDatatable'])->name('services.salesLines');
        Route::get('services/{service}/sales-stats', [ServiceController::class, 'salesStats'])->name('services.salesStats');
        Route::resource('services', ServiceController::class);

        Route::resource('packages', PackageController::class);
        Route::resource('package-subscriptions', PackageSubscriptionController::class);
        Route::resource('employees', EmployeeController::class);

        Route::get('points', [PointController::class, 'index'])->name('points.index');
        Route::get('points/create', [PointController::class, 'create'])->name('points.create');
        Route::post('points', [PointController::class, 'store'])->name('points.store');
        Route::get('points/wallet-info/{user}', [PointController::class, 'walletInfo'])->name('points.walletInfo');

        Route::get('products/{product}/sales-lines', [ProductController::class, 'salesLinesDatatable'])->name('products.salesLines');
        Route::get('products/{product}/sales-stats', [ProductController::class, 'salesStats'])->name('products.salesStats');
        Route::resource('products', ProductController::class);

        Route::prefix('wallets')->name('wallets.')->group(function () {

            Route::get('/', [WalletController::class, 'index'])->name('index');
            Route::get('datatable', [WalletController::class, 'datatable'])->name('datatable');
            Route::get('create', [WalletController::class, 'create'])->name('create');
            Route::post('/', [WalletController::class, 'store'])->name('store');
            Route::get('wallet-info/{user}', [WalletController::class, 'walletInfo'])->name('wallet_info');

        });

    });

});

require __DIR__ . '/auth.php';
