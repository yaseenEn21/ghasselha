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
use App\Http\Controllers\Dashboard\PromotionController;
use App\Http\Controllers\Dashboard\PromotionCouponController;
use App\Http\Controllers\Dashboard\InvoiceController;
use App\Http\Controllers\Dashboard\PaymentController;
use App\Http\Controllers\Dashboard\ZoneController;
use App\Http\Controllers\Dashboard\CustomerGroupController;
use App\Http\Controllers\Dashboard\CustomerGroupServicePriceController;
use App\Http\Controllers\Dashboard\ZoneServicePriceController;
use App\Http\Controllers\Dashboard\BookingController;
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

        Route::get('bookings', [BookingController::class, 'index'])->name('bookings.index');
        Route::get('bookings/datatable', [BookingController::class, 'datatable'])->name('bookings.datatable');
        Route::get('bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');
        Route::patch('bookings/{booking}/status', [BookingController::class, 'updateStatus'])
            ->name('bookings.status.update');

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



        // Promotions
        Route::get('promotions/datatable', [PromotionController::class, 'datatable'])->name('promotions.datatable');
        Route::get('promotions/search/services', [PromotionController::class, 'searchServices'])->name('promotions.search.services');
        Route::get('promotions/search/packages', [PromotionController::class, 'searchPackages'])->name('promotions.search.packages');
        Route::resource('promotions', PromotionController::class);

        // Coupons (edit/update/delete) + Redemptions
        Route::get('promotion-coupons/{coupon}/edit', [PromotionCouponController::class, 'edit'])->name('promotion_coupons.edit');
        Route::put('promotion-coupons/{coupon}', [PromotionCouponController::class, 'update'])->name('promotion_coupons.update');
        Route::delete('promotion-coupons/{coupon}', [PromotionCouponController::class, 'destroy'])->name('promotion_coupons.destroy');

        Route::get('promotion-coupons/{coupon}/redemptions', [PromotionCouponController::class, 'redemptions'])->name('promotion_coupons.redemptions');
        Route::get('promotion-coupons/{coupon}/redemptions/datatable', [PromotionCouponController::class, 'redemptionsDatatable'])->name('promotion_coupons.redemptions.datatable');

        // ===== Coupons =====
        Route::get('promotions/{promotion}/coupons', [PromotionCouponController::class, 'index'])
            ->name('promotions.coupons.index');

        Route::get('promotions/{promotion}/coupons/datatable', [PromotionCouponController::class, 'datatable'])
            ->name('promotions.coupons.datatable');

        Route::get('promotions/{promotion}/coupons/create', [PromotionCouponController::class, 'create'])
            ->name('promotions.coupons.create');

        Route::post('promotions/{promotion}/coupons', [PromotionCouponController::class, 'store'])
            ->name('promotions.coupons.store');

        Route::get('promotions/{promotion}/coupons/{coupon}/edit', [PromotionCouponController::class, 'edit'])
            ->name('promotions.coupons.edit');

        Route::put('promotions/{promotion}/coupons/{coupon}', [PromotionCouponController::class, 'update'])
            ->name('promotions.coupons.update');

        Route::delete('promotions/{promotion}/coupons/{coupon}', [PromotionCouponController::class, 'destroy'])
            ->name('promotions.coupons.destroy');

        // ===== Redemptions =====
        Route::get('promotions/{promotion}/coupons/{coupon}/redemptions', [PromotionCouponController::class, 'redemptions'])
            ->name('promotions.coupons.redemptions');

        Route::get('promotions/{promotion}/coupons/{coupon}/redemptions/datatable', [PromotionCouponController::class, 'redemptionsDatatable'])
            ->name('promotions.coupons.redemptions.datatable');

        Route::get('invoices/datatable', [InvoiceController::class, 'datatable'])->name('invoices.datatable');
        Route::resource('invoices', InvoiceController::class)->only(['index', 'show']);

        Route::get('payments/datatable', [PaymentController::class, 'datatable'])->name('payments.datatable');
        Route::resource('payments', PaymentController::class)->only(['index', 'show']);

        Route::get('customer-groups/datatable', [CustomerGroupController::class, 'datatable'])
            ->name('customer-groups.datatable');

        Route::resource('customer-groups', CustomerGroupController::class)
            ->parameters(['customer-groups' => 'customer_group'])
            ->names('customer-groups');

        Route::resource('customer-groups', CustomerGroupController::class);

        // Service Prices (Ajax)
        Route::get('customer-groups/{customerGroup}/service-prices/datatable', [CustomerGroupServicePriceController::class, 'datatable'])
            ->name('customer-groups.service-prices.datatable');

        Route::get('customer-groups/{customerGroup}/service-prices/{servicePrice}', [CustomerGroupServicePriceController::class, 'show'])
            ->name('customer-groups.service-prices.show');

        Route::post('customer-groups/{customerGroup}/service-prices', [CustomerGroupServicePriceController::class, 'store'])
            ->name('customer-groups.service-prices.store');

        Route::put('customer-groups/{customerGroup}/service-prices/{servicePrice}', [CustomerGroupServicePriceController::class, 'update'])
            ->name('customer-groups.service-prices.update');

        Route::delete('customer-groups/{customerGroup}/service-prices/{servicePrice}', [CustomerGroupServicePriceController::class, 'destroy'])
            ->name('customer-groups.service-prices.destroy');

        // Select2 services (exclude already assigned)
        Route::get('customer-groups/{customerGroup}/services/search', [CustomerGroupServicePriceController::class, 'searchServices'])
            ->name('customer-groups.services.search');


        Route::get('zones/datatable', [ZoneController::class, 'datatable'])->name('zones.datatable');
        Route::resource('zones', ZoneController::class)->names('zones');
        // Zone Service Prices (inside zone show)
        Route::get('zones/{zone}/service-prices/services/search', [ZoneServicePriceController::class, 'searchServices'])
            ->name('zones.service_prices.search.services');

        Route::post('zones/{zone}/service-prices', [ZoneServicePriceController::class, 'store'])
            ->name('zones.service_prices.store');

        Route::get('zones/{zone}/service-prices/{servicePrice}', [ZoneServicePriceController::class, 'show'])
            ->name('zones.service_prices.show');

        Route::put('zones/{zone}/service-prices/{servicePrice}', [ZoneServicePriceController::class, 'update'])
            ->name('zones.service_prices.update');

        Route::delete('zones/{zone}/service-prices/{servicePrice}', [ZoneServicePriceController::class, 'destroy'])
            ->name('zones.service_prices.destroy');

    });

});

require __DIR__ . '/auth.php';
