<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\BookingPackageController;
use App\Http\Controllers\Api\BookingRatingController;
use App\Http\Controllers\Api\PointController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ServiceCategoryController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\PackageController;
use App\Http\Controllers\Api\MyPackageController;
use App\Http\Controllers\Api\PackagePurchaseController;
use App\Http\Controllers\Api\VehicleMakeController;
use App\Http\Controllers\Api\VehicleModelController;
use App\Http\Controllers\Api\CarColorController;
use App\Http\Controllers\Api\MyCarController;
use App\Http\Controllers\Api\MyAddressController;
use App\Http\Controllers\Api\ProductCategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\SlotController;
use App\Http\Controllers\Api\WalletController;
use App\Http\Controllers\Api\WalletTransactionController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\InvoicePaymentController;
use App\Http\Controllers\Api\CouponController;
use App\Http\Controllers\Api\InvoiceCouponController;
use App\Http\Controllers\Api\MoyasarWebhookController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;


Route::prefix('v1')->middleware(['set.api.locale'])->group(function () {

    Route::post('moyasar/webhook', [MoyasarWebhookController::class, 'handle']);
    Route::get('moyasar/callback', [MoyasarWebhookController::class, 'callback']);
    Route::get('moyasar/success', [MoyasarWebhookController::class, 'success']);

    // Auth
    Route::prefix('auth')->group(function () {
        Route::post('send-otp', [AuthController::class, 'sendOtp']);
        Route::post('verify-otp', [AuthController::class, 'verifyOtp']);
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('logout', [AuthController::class, 'logout']);
            Route::post('logout-all', [AuthController::class, 'logoutAll']);
        });
    });

    Route::middleware('auth:sanctum')->group(function () {

        Route::get('profile', [ProfileController::class, 'show']);
        Route::post('profile', [ProfileController::class, 'update']);
        Route::delete('profile/image', [ProfileController::class, 'deleteProfileImage']);

        Route::get('bookings/package-eligibility', [BookingPackageController::class, 'eligibility']);

        Route::post('bookings', [BookingController::class, 'store']);
        Route::patch('bookings/{booking}/reschedule', [BookingController::class, 'reschedule']);
        Route::get('bookings/{booking}/products-edit', [BookingController::class, 'productsEdit']);
        Route::patch('bookings/{booking}/products', [BookingController::class, 'updateProducts']);
        Route::delete('bookings/{booking}', [BookingController::class, 'cancel']);
        Route::get('bookings', [BookingController::class, 'index']);
        Route::get('bookings/{booking}', [BookingController::class, 'show']);
        Route::get('bookings/{booking}/rating', [BookingRatingController::class, 'show']);
        Route::post('bookings/{booking}/rating', [BookingRatingController::class, 'store']);

        Route::get('my-cars', [MyCarController::class, 'index']);
        Route::post('my-cars', [MyCarController::class, 'store']);
        Route::get('my-cars/{car}', [MyCarController::class, 'show']);
        Route::put('my-cars/{car}', [MyCarController::class, 'update']);
        Route::delete('my-cars/{car}', [MyCarController::class, 'destroy']);

        Route::get('my-addresses', [MyAddressController::class, 'index']);
        Route::post('my-addresses', [MyAddressController::class, 'store']);
        Route::get('my-addresses/{address}', [MyAddressController::class, 'show']);
        Route::put('my-addresses/{address}', [MyAddressController::class, 'update']);
        Route::delete('my-addresses/{address}', [MyAddressController::class, 'destroy']);

        // My packages (auth)
        Route::get('my-packages', [MyPackageController::class, 'index']);

        // Purchase (auth)
        Route::post('packages/{package}/purchase', [PackagePurchaseController::class, 'store']);

        // Wallet
        Route::get('wallet', [WalletController::class, 'show']);
        Route::post('wallet/store', [WalletController::class, 'store']);
        Route::get('wallet/transactions', [WalletTransactionController::class, 'index']);

        // Invoices
        Route::get('invoices', [InvoiceController::class, 'index']);
        Route::get('invoices/{invoice}', [InvoiceController::class, 'show']);

        // Payments
        Route::get('payments', [PaymentController::class, 'index']);
        Route::get('payments/{payment}', [PaymentController::class, 'show']);
        // إنشاء دفعة لفاتورة (Wallet أو Pending لبوابة الدفع)
        Route::post('invoices/{invoice}/payments', [InvoicePaymentController::class, 'store']);

        // Coupons
        Route::get('coupons', [CouponController::class, 'index']); // ?tab=available|used
        Route::post('invoices/{invoice}/coupon/preview', [InvoiceCouponController::class, 'preview']);
        Route::post('invoices/{invoice}/coupon/apply', [InvoiceCouponController::class, 'apply']);
        Route::delete('invoices/{invoice}/coupon', [InvoiceCouponController::class, 'destroy']);

        Route::get('points', [PointController::class, 'show']);
        Route::get('points/transactions', action: [PointController::class, 'transactions']);
        Route::post('points/redeem/preview', [PointController::class, 'previewRedeem']);
        Route::post('points/redeem', [PointController::class, 'redeem']);


    });

    Route::get('slots', [SlotController::class, 'index']);

    Route::get('service-categories', [ServiceCategoryController::class, 'index']);
    Route::get('service-categories/{serviceCategory}', [ServiceCategoryController::class, 'show']);

    Route::get('services', [ServiceController::class, 'index']);
    Route::get('services/{service}', [ServiceController::class, 'show']);

    // Public browse
    Route::get('packages', [PackageController::class, 'index']);
    Route::get('packages/{package}', [PackageController::class, 'show']);



    Route::get('product-categories', [ProductCategoryController::class, 'index']);

    Route::get('products', [ProductController::class, 'index']);
    Route::get('products/{product}', [ProductController::class, 'show']);


    Route::get('vehicle-makes', [VehicleMakeController::class, 'index']);
    Route::get('vehicle-models', [VehicleModelController::class, 'index']); // ?vehicle_make_id=...
    Route::get('car-colors', [CarColorController::class, 'index']);


});