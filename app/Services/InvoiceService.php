<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Models\Service;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InvoiceService
{
    private function newInvoiceNumber(): string
    {
        return 'INV-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6));
    }

    private function nextVersionFor(string $type, int $id): int
    {
        return (int) (Invoice::query()
                ->where('invoiceable_type', $type)
                ->where('invoiceable_id', $id)
                ->max('version') ?? 0) + 1;
    }

    public function createBookingInvoice(Booking $booking, int $actorId): Invoice
    {
        return DB::transaction(function () use ($booking, $actorId) {

            $booking->loadMissing(['service','products.product']);

            $version = $this->nextVersionFor(Booking::class, $booking->id);

            $invoice = Invoice::create([
                'number' => $this->newInvoiceNumber(),
                'user_id' => $booking->user_id,

                'invoiceable_type' => Booking::class,
                'invoiceable_id' => $booking->id,

                'type' => 'invoice',
                'version' => $version,
                'status' => 'unpaid',

                'subtotal' => 0,
                'discount' => 0,
                'tax' => 0,
                'total' => 0,

                'currency' => $booking->currency,

                'meta' => [
                    'purpose' => 'booking_invoice',
                    'booking_id' => $booking->id,
                ],

                'created_by' => $actorId,
                'updated_by' => $actorId,
            ]);

            $subtotal = 0;

            // 1) Service line (إذا الخدمة عليها مبلغ)
            if ((float)$booking->service_final_price_snapshot > 0) {
                $svc = $booking->service;

                $unit = (float)$booking->service_charge_amount_snapshot;
                $lineTotal = $unit;

                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'item_type' => 'service',

                    'itemable_type' => Service::class,
                    'itemable_id' => $svc?->id,

                    'title' => $svc?->name,
                    'description' => $svc?->description,

                    'qty' => 1,
                    'unit_price' => $unit,
                    'line_tax' => 0,
                    'line_total' => $lineTotal,

                    'meta' => [
                        'booking_date' => $booking->booking_date->format('Y-m-d'),
                        'start_time' => substr((string)$booking->start_time,0,5),
                    ],
                    'sort_order' => 1,
                ]);

                $subtotal += $lineTotal;
            }

            // 2) Products lines
            $sort = 10;
            foreach ($booking->products as $bp) {
                $unit = (float)$bp->unit_price_snapshot;
                $qty = (int)$bp->qty;
                $lineTotal = (float)$bp->line_total;

                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'item_type' => 'product',

                    'itemable_type' => Product::class,
                    'itemable_id' => $bp->product_id,

                    'title' => $bp->title,
                    'description' => null,

                    'qty' => $qty,
                    'unit_price' => $unit,
                    'line_tax' => 0,
                    'line_total' => $lineTotal,

                    'meta' => [],
                    'sort_order' => $sort++,
                ]);

                $subtotal += $lineTotal;
            }

            $total = max(0, $subtotal - (float)$invoice->discount + (float)$invoice->tax);

            $invoice->update([
                'subtotal' => $subtotal,
                'total' => $total,
                'updated_by' => $actorId,
            ]);

            return $invoice->fresh(['items']);
        });
    }

    /**
     * لو الحجز pending ولسا ما دفع، بنعيد بناء آخر فاتورة unpaid بدل ما نعمل فواتير متعددة.
     */
    public function syncBookingUnpaidInvoice(Booking $booking, int $actorId): ?Invoice
    {
        return DB::transaction(function () use ($booking, $actorId) {

            $invoice = Invoice::query()
                ->where('invoiceable_type', Booking::class)
                ->where('invoiceable_id', $booking->id)
                ->where('status', 'unpaid')
                ->orderByDesc('id')
                ->lockForUpdate()
                ->first();

            if (!$invoice) {
                return null;
            }

            // امسح البنود وأعد بناءها
            InvoiceItem::query()->where('invoice_id', $invoice->id)->delete();

            $booking->loadMissing(['service','products.product']);

            $subtotal = 0;

            if ((float)$booking->service_final_price_snapshot > 0) {
                $svc = $booking->service;
                $unit = (float)$booking->service_final_price_snapshot;

                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'item_type' => 'service',
                    'itemable_type' => Service::class,
                    'itemable_id' => $svc?->id,
                    'title' => $svc?->name,
                    'description' => $svc?->description,
                    'qty' => 1,
                    'unit_price' => $unit,
                    'line_tax' => 0,
                    'line_total' => $unit,
                    'sort_order' => 1,
                ]);

                $subtotal += $unit;
            }

            $sort = 10;
            foreach ($booking->products as $bp) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'item_type' => 'product',
                    'itemable_type' => Product::class,
                    'itemable_id' => $bp->product_id,
                    'title' => $bp->title,
                    'description' => null,
                    'qty' => (int)$bp->qty,
                    'unit_price' => (float)$bp->unit_price_snapshot,
                    'line_tax' => 0,
                    'line_total' => (float)$bp->line_total,
                    'sort_order' => $sort++,
                ]);

                $subtotal += (float)$bp->line_total;
            }

            $total = max(0, $subtotal - (float)$invoice->discount + (float)$invoice->tax);

            $invoice->update([
                'subtotal' => $subtotal,
                'total' => $total,
                'updated_by' => $actorId,
            ]);

            return $invoice->fresh(['items']);
        });
    }

    /**
     * فاتورة “زيادة منتجات” بعد التأكيد (Delta only)
     */
    public function createBookingProductsDeltaInvoice(Booking $booking, array $deltaItems, int $actorId): Invoice
    {
        return DB::transaction(function () use ($booking, $deltaItems, $actorId) {

            $version = $this->nextVersionFor(Booking::class, $booking->id);

            $invoice = Invoice::create([
                'number' => $this->newInvoiceNumber(),
                'user_id' => $booking->user_id,
                'invoiceable_type' => Booking::class,
                'invoiceable_id' => $booking->id,
                'type' => 'adjustment',
                'version' => $version,
                'status' => 'unpaid',
                'subtotal' => 0,
                'discount' => 0,
                'tax' => 0,
                'total' => 0,
                'currency' => $booking->currency,
                'meta' => [
                    'purpose' => 'booking_products_delta',
                    'booking_id' => $booking->id,
                ],
                'created_by' => $actorId,
                'updated_by' => $actorId,
            ]);

            $subtotal = 0;
            $sort = 10;

            foreach ($deltaItems as $it) {
                // it: [product_id, qty_delta, unit_price, title_json]
                $lineTotal = (float)$it['qty'] * (float)$it['unit_price'];
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'item_type' => 'product',
                    'itemable_type' => Product::class,
                    'itemable_id' => (int)$it['product_id'],
                    'title' => $it['title'],
                    'qty' => (float)$it['qty'],
                    'unit_price' => (float)$it['unit_price'],
                    'line_tax' => 0,
                    'line_total' => $lineTotal,
                    'sort_order' => $sort++,
                    'meta' => ['delta' => true],
                ]);

                $subtotal += $lineTotal;
            }

            $invoice->update([
                'subtotal' => $subtotal,
                'total' => $subtotal,
                'updated_by' => $actorId,
            ]);

            return $invoice->fresh(['items']);
        });
    }

    public function createBookingCreditNoteToWallet(Booking $booking, float $refundAmount, int $actorId, array $meta = []): Invoice
    {
        return DB::transaction(function () use ($booking, $refundAmount, $actorId, $meta) {

            $version = $this->nextVersionFor(Booking::class, $booking->id);

            $invoice = Invoice::create([
                'number' => $this->newInvoiceNumber(),
                'user_id' => $booking->user_id,
                'invoiceable_type' => Booking::class,
                'invoiceable_id' => $booking->id,
                'type' => 'credit_note',
                'version' => $version,
                'status' => 'paid', // لأنه رح نرجّع للمحفظة فورًا
                'subtotal' => 0,
                'discount' => 0,
                'tax' => 0,
                'total' => $refundAmount,
                'currency' => $booking->currency,
                'paid_at' => now(),
                'is_locked' => true,
                'meta' => array_merge([
                    'purpose' => 'booking_refund_wallet',
                    'booking_id' => $booking->id,
                    'refund_to_wallet' => true,
                ], $meta),
                'created_by' => $actorId,
                'updated_by' => $actorId,
            ]);

            return $invoice;
        });
    }
}