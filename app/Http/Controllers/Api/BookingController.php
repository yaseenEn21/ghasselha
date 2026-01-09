<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\BookingCancelRequest;
use App\Http\Requests\Api\BookingProductsUpdateRequest;
use App\Http\Requests\Api\BookingRescheduleRequest;
use App\Http\Requests\Api\BookingStoreRequest;
use App\Http\Resources\Api\BookingProductResource;
use App\Http\Resources\Api\BookingResource;
use App\Http\Resources\Api\ProductResource;
use App\Jobs\AutoCancelPendingBookingJob;
use App\Models\Address;
use App\Models\Booking;
use App\Models\BookingProduct;
use App\Models\Car;
use App\Models\Product;
use App\Models\Service;
use App\Services\BookingCancellationService;
use App\Services\InvoiceService;
use App\Services\SlotService;
use App\Services\WalletService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    /**
     * GET /api/v1/bookings?status=pending&from=01-12-2025&to=31-12-2025
     */
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user)
            return api_error('Unauthenticated', 401);

        $q = Booking::query()
            ->where('user_id', $user->id)
            ->with(['service', 'products']);

        if ($request->filled('status')) {
            $q->where('status', $request->input('status'));
        }

        if ($request->filled('from')) {
            $from = Carbon::createFromFormat('d-m-Y', $request->input('from'))->toDateString();
            $q->where('booking_date', '>=', $from);
        }

        if ($request->filled('to')) {
            $to = Carbon::createFromFormat('d-m-Y', $request->input('to'))->toDateString();
            $q->where('booking_date', '<=', $to);
        }

        $p = $q->orderByDesc('booking_date')->orderByDesc('start_time')->paginate(20);

        $p->setCollection($p->getCollection()->map(fn($b) => new BookingResource($b)));

        return api_paginated($p, 'Bookings');
    }

    public function show(Request $request, Booking $booking)
    {
        $user = $request->user();
        if (!$user)
            return api_error('Unauthenticated', 401);
        if ($booking->user_id !== $user->id)
            return api_error('Not found', 404);

        $booking->load(['service', 'products', 'invoices']);

        return api_success(new BookingResource($booking), 'Booking');
    }

    /**
     * POST /api/v1/bookings
     */
    public function store(
        BookingStoreRequest $request,
        SlotService $slotService,
        InvoiceService $invoiceService
    ) {
        $user = $request->user();
        if (!$user)
            return api_error('Unauthenticated', 401);

        $data = $request->validated();

        $car = Car::query()->where('id', $data['car_id'])->where('user_id', $user->id)->first();
        if (!$car)
            return api_error('Car not found', 404);

        $address = Address::query()->where('id', $data['address_id'])->where('user_id', $user->id)->first();
        if (!$address)
            return api_error('Address not found', 404);

        $service = Service::query()->where('id', $data['service_id'])->where('is_active', true)->first();
        if (!$service)
            return api_error('Service not found', 404);

        $day = Carbon::createFromFormat('d-m-Y', $data['date']);
        $dbDate = $day->toDateString();

        $startTime = $data['time']; // HH:MM
        $duration = (int) $service->duration_minutes;

        $endTime = Carbon::createFromFormat('Y-m-d H:i', $dbDate . ' ' . $startTime)
            ->addMinutes($duration)
            ->format('H:i');

        // ✅ تحقق slot متاح + احصل موظفين
        $slots = $slotService->getSlots($data['date'], (int) $service->id, (float) $address->lat, (float) $address->lng);

        // ✅ لو ما في slots أصلاً: رجّع رسالة حسب السبب
        if (empty($slots['items'])) {
            $code = $slots['meta']['error_code'] ?? null;

            if ($code === 'OUT_OF_COVERAGE') {
                return api_error('Address is outside service coverage area', 422);
            }

            if ($code === 'NO_WORKING_HOURS') {
                return api_error('No working hours available for the selected date', 422);
            }

            return api_error('No slots available for the selected date', 422);
        }

        // بعدها افحص الوقت المحدد
        $slot = collect($slots['items'])->first(fn($s) => ($s['start_time'] ?? null) === $startTime);
        if (!$slot) {
            return api_error('Selected time is not available', 422);
        }

        $employees = $slot['employees'] ?? [];
        if (empty($employees))
            return api_error('No employee available for this slot', 422);

        $pickedEmployeeId = $data['employee_id'] ?? null;
        if ($pickedEmployeeId) {
            $found = collect($employees)->first(fn($e) => (int) $e['employee_id'] === (int) $pickedEmployeeId);
            if (!$found)
                return api_error('Selected employee is not available in this slot', 422);
        } else {
            $pickedEmployeeId = (int) $employees[0]['employee_id'];
        }

        $subscriptionId = $data['package_subscription_id'] ?? null;

        // أسعار الخدمة snapshot
        $price = (float) $service->price;
        $disc = $service->discounted_price !== null ? (float) $service->discounted_price : null;
        $final = $disc ?? $price;

        // لو عنده باقة تغطي الخدمة → final=0
        $meta = [];
        if ($subscriptionId) {
            $meta['package_covers_service'] = true;
            $final = 0;
        }

        $address = Address::query()
            ->where('id', $request->integer('address_id'))
            ->where('user_id', $user->id)
            ->firstOrFail();

        $service = Service::query()
            ->where('id', $request->integer('service_id'))
            ->where('is_active', true)
            ->firstOrFail();

        $pricing = app(\App\Services\BookingPricingService::class)
            ->resolve($service, $user, $address, $request->input('time'));

        // السعر الحقيقي للخدمة
        $final = (float) $pricing['final_unit_price'];

        // إذا الحجز باستخدام باقة صالحة تغطي الخدمة -> charge = 0
        $usingPackage = (bool) $request->filled('package_subscription_id'); // (مع تحققك الكامل)
        $chargeAmount = $usingPackage ? 0.0 : $final;

        $booking = DB::transaction(function () use ($user, $car, $address, $service, $dbDate, $startTime, $endTime, $duration, $price, $disc, $final, $subscriptionId, $pickedEmployeeId, $data, $meta, $invoiceService, $pricing, $chargeAmount, $usingPackage) {
            $booking = Booking::create([
                'user_id' => $user->id,
                'car_id' => $car->id,
                'address_id' => $address->id,
                'service_id' => $service->id,

                'zone_id' => $pricing['zone_id'],
                'time_period' => $pricing['time_period'],

                'service_unit_price_snapshot' => $pricing['unit_price'],
                'service_discounted_price_snapshot' => $pricing['discounted_price'],
                'service_final_price_snapshot' => $final,

                'service_charge_amount_snapshot' => $chargeAmount,
                'service_pricing_source' => $usingPackage ? 'package' : $pricing['pricing_source'],
                'service_pricing_meta' => [
                    'applied_id' => $pricing['applied_id'],
                    'lat' => (float) $address->lat,
                    'lng' => (float) $address->lng,
                ],

                'employee_id' => $pickedEmployeeId,
                'package_subscription_id' => $subscriptionId,

                'status' => 'pending',

                'booking_date' => $dbDate,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'duration_minutes' => $duration,

                'service_price_snapshot' => $price,

                'currency' => 'SAR',
                'meta' => $meta,

                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]);

            // Products
            $productsSubtotal = 0;
            $productsInput = $data['products'] ?? [];

            foreach ($productsInput as $p) {
                $prod = Product::query()->where('id', (int) $p['product_id'])->where('is_active', true)->first();
                if (!$prod)
                    continue;

                $qty = (int) $p['qty'];
                $unit = (float) $prod->price;
                $line = $qty * $unit;

                BookingProduct::create([
                    'booking_id' => $booking->id,
                    'product_id' => $prod->id,
                    'qty' => $qty,
                    'unit_price_snapshot' => $unit,
                    'title' => $prod->name, // JSON
                    'line_total' => $line,
                ]);

                $productsSubtotal += $line;
            }

            $subtotal = (float) $final + (float) $productsSubtotal;
            $total = $subtotal; // tax لاحقاً

            $booking->update([
                'products_subtotal_snapshot' => $productsSubtotal,
                'subtotal_snapshot' => $subtotal,
                'total_snapshot' => $total,
            ]);

            // ✅ إن كانت total = 0 → أكد الحجز مباشرة (باقة بدون منتجات)
            if ($total <= 0.0) {
                $m = (array) ($booking->meta ?? []);
                $m['package_deducted'] = false; // رح يتم خصمها عند التأكيد/fulfillment (اختياري)
                $booking->update([
                    'status' => 'confirmed',
                    'confirmed_at' => now(),
                    'meta' => $m,
                ]);
            } else {
                // ✅ اصدار فاتورة
                $invoiceService->createBookingInvoice($booking->fresh(['service', 'products']), $user->id);

                // ✅ Job إلغاء بعد 10 دقائق لو ما اندفع
                AutoCancelPendingBookingJob::dispatch($booking->id)
                    ->delay(now()->addMinutes((int) config('booking.pending_auto_cancel_minutes', 10)));
            }

            return $booking->fresh(['service', 'products', 'invoices']);
        });

        return api_success(new BookingResource($booking), 'Booking created', 201);
    }

    /**
     * PATCH /api/v1/bookings/{booking}/reschedule
     */
    public function reschedule(BookingRescheduleRequest $request, Booking $booking, SlotService $slotService)
    {
        $user = $request->user();
        if (!$user)
            return api_error('Unauthenticated', 401);
        if ($booking->user_id !== $user->id)
            return api_error('Not found', 404);

        if (in_array($booking->status, ['cancelled', 'completed'], true)) {
            return api_error('Booking cannot be rescheduled', 409);
        }

        // شرط 100 دقيقة
        $startAt = Carbon::parse($booking->booking_date->format('Y-m-d') . ' ' . $booking->start_time);
        if (now()->diffInMinutes($startAt, false) < (int) config('booking.reschedule_min_minutes', 100)) {
            return api_error('Reschedule not allowed (too late)', 409);
        }

        $data = $request->validated();

        $address = $booking->address()->first();
        if (!$address)
            return api_error('Address missing', 409);

        $service = $booking->service()->first();
        if (!$service)
            return api_error('Service missing', 409);

        $duration = (int) $booking->duration_minutes;

        $day = Carbon::createFromFormat('d-m-Y', $data['date']);
        $dbDate = $day->toDateString();

        $startTime = $data['time'];
        $endTime = Carbon::createFromFormat('Y-m-d H:i', $dbDate . ' ' . $startTime)
            ->addMinutes($duration)
            ->format('H:i');

        $slots = $slotService->getSlots($data['date'], (int) $service->id, (float) $address->lat, (float) $address->lng);

        $slot = collect($slots['items'] ?? [])->first(fn($s) => ($s['start_time'] ?? null) === $startTime);
        if (!$slot)
            return api_error('Selected time is not available', 422);

        $employees = $slot['employees'] ?? [];
        if (empty($employees))
            return api_error('No employee available for this slot', 422);

        $pickedEmployeeId = $data['employee_id'] ?? (int) $employees[0]['employee_id'];
        $found = collect($employees)->first(fn($e) => (int) $e['employee_id'] === (int) $pickedEmployeeId);
        if (!$found)
            return api_error('Selected employee is not available in this slot', 422);

        $booking->update([
            'booking_date' => $dbDate,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'employee_id' => $pickedEmployeeId,
            'updated_by' => $user->id,
        ]);

        $booking->load(['service', 'products', 'invoices']);

        return api_success(new BookingResource($booking), 'Booking rescheduled');
    }

    public function productsEdit(Request $request, Booking $booking)
    {
        $user = $request->user();
        if (!$user)
            return api_error('Unauthenticated', 401);
        if ($booking->user_id !== $user->id)
            return api_error('Not found', 404);

        $products = Product::query()
            ->where('is_active', true)
            ->orderBy('id', 'desc')
            ->get();

        $booking->load(['products.product']); // علاقة booking_products + product

        return api_success([
            'products' => ProductResource::collection($products),
            'selected' => BookingProductResource::collection($booking->products),

            // (اختياري) للمراجعة
            'totals' => [
                'service_charge_amount' => number_format((float) $booking->service_charge_amount_snapshot, 2, '.', ''),
                'products_subtotal' => number_format((float) $booking->products_subtotal_snapshot, 2, '.', ''),
                'total' => number_format((float) $booking->total_snapshot, 2, '.', ''),
            ],
        ], 'Booking products edit data');
    }

    /**
     * PUT /api/v1/bookings/{booking}/products
     */
    public function updateProducts(
        BookingProductsUpdateRequest $request,
        Booking $booking,
        InvoiceService $invoiceService,
        WalletService $walletService
    ) {
        $user = $request->user();
        if (!$user)
            return api_error('Unauthenticated', 401);
        if ($booking->user_id !== $user->id)
            return api_error('Not found', 404);

        if (in_array($booking->status, ['cancelled', 'completed'], true)) {
            return api_error('Booking cannot be updated', 409);
        }

        // منع التعديل إذا فيه فاتورة unpaid موجودة (لو confirmed وبعدها طلب زيادة، لازم يدفع أولاً)
        $hasUnpaid = $booking->invoices()
            ->where('status', 'unpaid')
            ->exists();

        if ($booking->status !== 'pending' && $hasUnpaid) {
            return api_error('You have an unpaid invoice for this booking', 409);
        }

        $data = $request->validated();

        $booking = DB::transaction(function () use ($booking, $data, $invoiceService, $walletService, $user) {

            $booking = Booking::query()->where('id', $booking->id)->lockForUpdate()->firstOrFail();

            $oldProducts = (float) $booking->products_subtotal_snapshot;

            // sync booking_products
            $incoming = collect($data['products'])->keyBy('product_id');

            if ($incoming->isEmpty()) {

                BookingProduct::query()
                    ->where('booking_id', $booking->id)
                    ->delete();

                $newProductsSubtotal = 0;

            } else {

                // delete removed
                BookingProduct::query()
                    ->where('booking_id', $booking->id)
                    ->whereNotIn('product_id', $incoming->keys()->all())
                    ->delete();

                $newProductsSubtotal = 0;

                foreach ($incoming as $pid => $row) {
                    $prod = Product::query()->where('id', (int) $pid)->where('is_active', true)->first();
                    if (!$prod)
                        continue;

                    $qty = (int) $row['qty'];
                    $unit = (float) $prod->price;
                    $line = $qty * $unit;

                    BookingProduct::query()->updateOrCreate(
                        ['booking_id' => $booking->id, 'product_id' => (int) $pid],
                        [
                            'qty' => $qty,
                            'unit_price_snapshot' => $unit,
                            'title' => $prod->name,
                            'line_total' => $line,
                        ]
                    );

                    $newProductsSubtotal += $line;
                }

            }

            // totals update
            $serviceFinal = (float) $booking->service_final_price_snapshot;
            $newTotal = $serviceFinal + $newProductsSubtotal;

            $booking->update([
                'products_subtotal_snapshot' => $newProductsSubtotal,
                'subtotal_snapshot' => $newTotal,
                'total_snapshot' => $newTotal,
                'updated_by' => $user->id,
            ]);

            $diff = $newProductsSubtotal - $oldProducts;

            // ✅ لو pending: حدّث آخر فاتورة unpaid بدل ما تعمل فاتورة جديدة
            if ($booking->status === 'pending') {
                $invoiceService->syncBookingUnpaidInvoice($booking->fresh(['service', 'products']), $user->id);
                return $booking->fresh(['service', 'products', 'invoices']);
            }

            // ✅ لو confirmed:
            if (abs($diff) < 0.0001) {
                return $booking->fresh(['service', 'products', 'invoices']);
            }

            if ($diff > 0) {
                // delta invoice
                $deltaItems = [];
                // أبسط دقة: اصدر فاتورة بقيمة diff كبند custom واحد (لكن الأفضل delta per product)
                // هنا رح نعمل delta per product مقارنةً بالقديم بشكل سريع:
                $current = BookingProduct::query()->where('booking_id', $booking->id)->get()->keyBy('product_id');

                // مبدئيًا: اصدر بند واحد custom (سريع وعملي)
                $deltaItems[] = [
                    'product_id' => (int) ($current->first()?->product_id ?? 0),
                    'qty' => 1,
                    'unit_price' => (float) $diff,
                    'title' => ['ar' => 'إضافة منتجات', 'en' => 'Products update'],
                ];

                $invoiceService->createBookingProductsDeltaInvoice($booking, $deltaItems, $user->id);

            } else {
                // refund to wallet
                $refund = abs((float) $diff);
                $walletService->credit(
                    $user,
                    $refund,
                    'refund',
                    ['ar' => 'استرجاع فرق منتجات', 'en' => 'Products refund difference'],
                    $booking,
                    null,
                    $user->id,
                    ['booking_id' => $booking->id]
                );

                $invoiceService->createBookingCreditNoteToWallet($booking, $refund, $user->id, [
                    'reason' => 'products_decrease',
                ]);
            }

            return $booking->fresh(['service', 'products', 'invoices']);
        });

        return api_success(new BookingResource($booking), 'Booking products updated');
    }

    /**
     * POST /api/v1/bookings/{booking}/cancel
     */
    public function cancel(
        BookingCancelRequest $request,
        Booking $booking,
        BookingCancellationService $cancellationService,
        WalletService $walletService,
        InvoiceService $invoiceService
    ) {
        $user = $request->user();
        if (!$user)
            return api_error('Unauthenticated', 401);
        if ($booking->user_id !== $user->id)
            return api_error('Not found', 404);

        if ($booking->status === 'cancelled') {
            return api_success(new BookingResource($booking->load(['service', 'products', 'invoices'])), 'Already cancelled');
        }

        // شرط 120 دقيقة
        $startAt = Carbon::parse($booking->booking_date->format('Y-m-d') . ' ' . $booking->start_time);
        if (now()->diffInMinutes($startAt, false) < (int) config('booking.cancel_min_minutes', 120)) {
            return api_error('Cancel not allowed (too late)', 409);
        }

        $data = $request->validated();

        $booking = $cancellationService->cancel(
            $booking,
            $data['reason'],
            $data['note'] ?? null,
            $user->id,
            $walletService,
            $invoiceService
        );

        $booking->load(['service', 'products', 'invoices']);

        return api_success(new BookingResource($booking), 'Booking cancelled');
    }
}