<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Package;
use App\Models\PackageSubscription;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class PackagePurchaseService
{
    public function createInvoiceForPackage(int $userId, Package $package, int $actorId): Invoice
    {
        return DB::transaction(function () use ($userId, $package, $actorId) {

            $price = (float) $package->price;
            $disc = $package->discounted_price !== null ? (float) $package->discounted_price : null;
            $final = $disc ?? $price;

            // خصم على مستوى الفاتورة (حسب قرارك)
            $invoiceDiscount = $disc !== null ? max(0, $price - $final) : 0;

            $packageId = $package->id;

            // ابحث عن فواتير غير مدفوعة لنفس المستخدم ونفس الباقة
            $oldInvoices = Invoice::query()
                ->where('user_id', $userId)
                ->where('status', 'unpaid') // أو pending إن كان عندك
                ->where('invoiceable_type', Package::class)
                ->where('invoiceable_id', $packageId)
                ->whereNull('deleted_at')
                ->lockForUpdate()
                ->get();

            foreach ($oldInvoices as $old) {

                // لو عليها دفعات "paid" اتركها (ما بصير نلغيها)
                $hasPaid = Payment::query()
                    ->where('invoice_id', $old->id)
                    ->where('status', 'paid')
                    ->exists();

                if ($hasPaid) {
                    continue;
                }

                // لو عليها دفعات pending (رابط دفع مفتوح) الأفضل نلغيها بدل حذفها
                $old->update([
                    'status' => 'cancelled',
                    'is_locked' => true,
                    'updated_by' => $actorId,
                    'meta' => array_merge($old->meta ?? [], [
                        'cancel_reason' => 'Replaced by a newer invoice for same package',
                        'cancelled_at' => now()->toISOString(),
                    ]),
                ]);

            }

            $invoice = Invoice::create([
                'number' => $this->generateNumber(),
                'user_id' => $userId,

                // نربطها بالباقة نفسها (مؤقتًا)
                'invoiceable_type' => Package::class,
                'invoiceable_id' => $package->id,

                'type' => 'invoice',
                'status' => 'unpaid',

                'subtotal' => $price,
                'discount' => $invoiceDiscount,
                'tax' => 0,
                'total' => ($price - $invoiceDiscount),

                'currency' => 'SAR',
                'issued_at' => now(),
                'is_locked' => false,

                'meta' => [
                    'purpose' => 'package_purchase',
                    'package_id' => $package->id,

                    // snapshots مهمة عشان لو تغيّرت الباقة لاحقًا
                    'package_snapshot' => [
                        'price' => $price,
                        'discounted_price' => $disc,
                        'final_price' => $final,
                        'validity_days' => (int) $package->validity_days,
                        'washes_count' => (int) $package->washes_count,
                        'name' => $package->name,
                        'description' => $package->description,
                    ],
                ],

                'created_by' => $actorId,
                'updated_by' => $actorId,
            ]);

            // بند واحد: الباقة
            InvoiceItem::create([
                'invoice_id' => $invoice->id,

                // لو بدك تضيف item_type=package لاحقًا، الآن خليها custom
                'item_type' => 'custom',

                'itemable_type' => Package::class,
                'itemable_id' => $package->id,

                'title' => $package->name,         // عندك JSON متعدد لغات غالبًا
                'description' => $package->description,

                'qty' => 1,
                'unit_price' => $price,
                'line_tax' => 0,
                'line_total' => $price,

                'meta' => [
                    'final_price' => $final,
                    'discount_amount' => $invoiceDiscount,
                ],
                'sort_order' => 1,
            ]);

            return $invoice->load('items');
        });
    }

    /**
     * تُستدعى عندما تصبح الفاتورة Paid بالكامل
     * (idempotent)
     */
    public function fulfillPaidInvoice(Invoice $invoice, int $actorId): ?PackageSubscription
    {
        $purpose = data_get($invoice->meta, 'purpose');
        if ($purpose !== 'package_purchase')
            return null;

        // لو تم التنفيذ سابقًا
        $existingId = data_get($invoice->meta, 'subscription_id');
        if ($existingId) {
            return PackageSubscription::query()->find($existingId);
        }

        $snap = data_get($invoice->meta, 'package_snapshot', []);
        $packageId = (int) data_get($invoice->meta, 'package_id');

        if (!$packageId)
            return null;

        // منع إنشاء اشتراك لو عنده واحد فعّال لنفس الباقة
        $alreadyActive = PackageSubscription::query()
            ->where('user_id', $invoice->user_id)
            ->where('package_id', $packageId)
            ->active()
            ->exists();

        if ($alreadyActive) {
            // نعلّمها منفذة بدون إنشاء جديد
            $invoice->update([
                'meta' => array_merge($invoice->meta ?? [], ['subscription_id' => null, 'fulfilled' => true]),
                'updated_by' => $actorId,
            ]);
            return null;
        }

        $startsAt = now()->toDateString();
        $validityDays = (int) ($snap['validity_days'] ?? 0);
        $endsAt = now()->addDays($validityDays)->toDateString();

        $price = (float) ($snap['price'] ?? 0);
        $disc = $snap['discounted_price'] ?? null;
        $disc = $disc !== null ? (float) $disc : null;
        $final = (float) ($snap['final_price'] ?? ($disc ?? $price));

        $totalWashes = (int) ($snap['washes_count'] ?? 0);

        $sub = PackageSubscription::create([
            'user_id' => $invoice->user_id,
            'package_id' => $packageId,

            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'status' => 'active',

            'price_snapshot' => $price,
            'discounted_price_snapshot' => $disc,
            'final_price_snapshot' => $final,

            'total_washes_snapshot' => $totalWashes,
            'remaining_washes' => $totalWashes,

            'purchased_at' => now(),
            'created_by' => $actorId,
            'updated_by' => $actorId,
        ]);

        // اربط الفاتورة بالاشتراك (في meta) + اقفلها
        $meta = $invoice->meta ?? [];
        $meta['subscription_id'] = $sub->id;
        $meta['fulfilled'] = true;

        $invoice->update([
            'meta' => $meta,
            'is_locked' => true,
            'updated_by' => $actorId,
        ]);

        return $sub;
    }

    private function generateNumber(): string
    {
        return 'INV-' . now()->format('Ymd') . '-' . str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT);
    }
}