<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InvoicePaymentsTestSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        DB::transaction(function () use ($now) {

            // Actor (admin أو أول user)
            $actorId = DB::table('users')->where('user_type', 'admin')->value('id')
                ?? DB::table('users')->orderBy('id')->value('id');

            // Customer (أول customer أو user id 1)
            $customerId = DB::table('users')->where('user_type', 'customer')->value('id')
                ?? DB::table('users')->orderBy('id')->value('id');

            // خدمة ومنتج للاختبار
            $service = DB::table('services')->select('id', 'name', 'description')->orderBy('id')->first();
            $product = DB::table('products')->select('id', 'name', 'description')->orderBy('id')->first();

            if (!$service || !$product) {
                throw new \RuntimeException('Please seed services and products first.');
            }

            // -------- Invoice #1 (Paid) ----------
            $invNumber1 = 'INV-TEST-0001';

            $servicePrice = 70.00;
            $productPrice = 16.75;
            $productQty   = 1;

            $subtotal1 = round($servicePrice + ($productPrice * $productQty), 2); // 86.75
            $discount1 = 4.33;
            $tax1      = 0.00;
            $total1    = round(($subtotal1 + $tax1) - $discount1, 2); // 82.42

            DB::table('invoices')->updateOrInsert(
                ['number' => $invNumber1],
                [
                    'user_id' => $customerId,

                    'invoiceable_type' => null,
                    'invoiceable_id' => null,

                    'type' => 'invoice',
                    'parent_invoice_id' => null,
                    'version' => 1,

                    'status' => 'paid',

                    'subtotal' => $subtotal1,
                    'discount' => $discount1,
                    'tax' => $tax1,
                    'total' => $total1,

                    'currency' => 'SAR',
                    'issued_at' => $now,
                    'paid_at' => $now,
                    'is_locked' => true,

                    'meta' => json_encode([
                        'note' => 'Test invoice (service + product) with invoice-level discount',
                    ], JSON_UNESCAPED_UNICODE),

                    'created_by' => $actorId,
                    'updated_by' => $actorId,

                    'created_at' => $now,
                    'updated_at' => $now,
                    'deleted_at' => null,
                ]
            );

            $invoice1Id = DB::table('invoices')->where('number', $invNumber1)->value('id');

            // items invoice #1
            DB::table('invoice_items')->updateOrInsert(
                ['invoice_id' => $invoice1Id, 'sort_order' => 1],
                [
                    'item_type' => 'service',
                    'itemable_type' => 'App\\Models\\Service',
                    'itemable_id' => $service->id,

                    // names are JSON in your system -> نخزنها كما هي إن كانت JSON، وإلا نحولها
                    'title' => $this->asJsonTitle($service->name),
                    'description' => $this->asJsonDesc($service->description),

                    'qty' => 1,
                    'unit_price' => $servicePrice,
                    'line_tax' => 0,
                    'line_total' => $servicePrice,

                    'meta' => json_encode([], JSON_UNESCAPED_UNICODE),

                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );

            DB::table('invoice_items')->updateOrInsert(
                ['invoice_id' => $invoice1Id, 'sort_order' => 2],
                [
                    'item_type' => 'product',
                    'itemable_type' => 'App\\Models\\Product',
                    'itemable_id' => $product->id,

                    'title' => $this->asJsonTitle($product->name),
                    'description' => $this->asJsonDesc($product->description),

                    'qty' => $productQty,
                    'unit_price' => $productPrice,
                    'line_tax' => 0,
                    'line_total' => round($productPrice * $productQty, 2),

                    'meta' => json_encode(['qty' => $productQty], JSON_UNESCAPED_UNICODE),

                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );

            // Payment for invoice #1 (paid)
            DB::table('payments')->updateOrInsert(
                [
                    'invoice_id' => $invoice1Id,
                    'status' => 'paid',
                    'payable_type' => 'invoice_payment',
                ],
                [
                    'user_id' => $customerId,

                    'amount' => $total1,
                    'currency' => 'SAR',

                    'method' => 'credit_card',   // أو apple_pay / google_pay
                    'status' => 'paid',
                    'payable_type' => 'invoice_payment',

                    'gateway' => 'myfatoorah',
                    'gateway_payment_id' => 'TEST-PAY-0001',

                    'paid_at' => $now,

                    // لو عندك payable_type/id
                    'payable_id' => null,

                    'meta' => json_encode([
                        'invoice_number' => $invNumber1,
                        'note' => 'Test paid payment for invoice',
                    ], JSON_UNESCAPED_UNICODE),

                    'created_by' => $actorId,
                    'updated_by' => $actorId,
                    'created_at' => $now,
                    'updated_at' => $now,
                    'deleted_at' => null,
                ]
            );

            // -------- Invoice #2 (Unpaid) ----------
            $invNumber2 = 'INV-TEST-0002';

            $subtotal2 = 120.00;
            $discount2 = 0.00;
            $tax2      = 0.00;
            $total2    = 120.00;

            DB::table('invoices')->updateOrInsert(
                ['number' => $invNumber2],
                [
                    'user_id' => $customerId,

                    'invoiceable_type' => null,
                    'invoiceable_id' => null,

                    'type' => 'invoice',
                    'parent_invoice_id' => null,
                    'version' => 2,

                    'status' => 'unpaid',

                    'subtotal' => $subtotal2,
                    'discount' => $discount2,
                    'tax' => $tax2,
                    'total' => $total2,

                    'currency' => 'SAR',
                    'issued_at' => $now,
                    'paid_at' => null,
                    'is_locked' => false,

                    'meta' => json_encode([
                        'note' => 'Test unpaid invoice',
                    ], JSON_UNESCAPED_UNICODE),

                    'created_by' => $actorId,
                    'updated_by' => $actorId,

                    'created_at' => $now,
                    'updated_at' => $now,
                    'deleted_at' => null,
                ]
            );

            $invoice2Id = DB::table('invoices')->where('number', $invNumber2)->value('id');

            DB::table('invoice_items')->updateOrInsert(
                ['invoice_id' => $invoice2Id, 'sort_order' => 1],
                [
                    'item_type' => 'custom',
                    'itemable_type' => null,
                    'itemable_id' => null,

                    'title' => json_encode(['ar' => 'مبلغ مستحق', 'en' => 'Due amount'], JSON_UNESCAPED_UNICODE),
                    'description' => json_encode(['ar' => 'فاتورة اختبار غير مدفوعة', 'en' => 'Test unpaid invoice'], JSON_UNESCAPED_UNICODE),

                    'qty' => 1,
                    'unit_price' => $subtotal2,
                    'line_tax' => 0,
                    'line_total' => $subtotal2,

                    'meta' => json_encode([], JSON_UNESCAPED_UNICODE),

                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );

            // Payment pending for invoice #2
            DB::table('payments')->updateOrInsert(
                [
                    'invoice_id' => $invoice2Id,
                    'status' => 'pending',
                    'payable_type' => 'invoice_payment',
                ],
                [
                    'user_id' => $customerId,

                    'amount' => $total2,
                    'currency' => 'SAR',

                    'method' => 'apple_pay',
                    'status' => 'pending',
                    'payable_type' => 'invoice_payment',

                    'gateway' => 'myfatoorah',
                    'gateway_payment_id' => 'TEST-PAY-0002',

                    'paid_at' => null,
                    'payable_id' => null,

                    'meta' => json_encode([
                        'invoice_number' => $invNumber2,
                        'note' => 'Pending payment (gateway not integrated)',
                    ], JSON_UNESCAPED_UNICODE),

                    'created_by' => $actorId,
                    'updated_by' => $actorId,
                    'created_at' => $now,
                    'updated_at' => $now,
                    'deleted_at' => null,
                ]
            );

            // -------- Wallet Top-up Payment (paid) ----------
            DB::table('payments')->updateOrInsert(
                [
                    'user_id' => $customerId,
                    'payable_type' => 'wallet_topup',
                    'gateway_payment_id' => 'TEST-WALLET-0001',
                ],
                [
                    'invoice_id' => null,
                    'amount' => 100.00,
                    'currency' => 'SAR',

                    'method' => 'google_pay',
                    'status' => 'paid',
                    'payable_type' => 'wallet_topup',

                    'gateway' => 'myfatoorah',
                    'paid_at' => $now,

                    'payable_id' => null,

                    'meta' => json_encode([
                        'note' => 'Wallet top-up test',
                    ], JSON_UNESCAPED_UNICODE),

                    'created_by' => $actorId,
                    'updated_by' => $actorId,
                    'created_at' => $now,
                    'updated_at' => $now,
                    'deleted_at' => null,
                ]
            );
        });
    }

    private function asJsonTitle($value): string
    {
        // لو القيمة JSON أصلاً نخليها
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $value;
            }
        }
        // غير ذلك: نحوله لعنوان بسيط
        return json_encode(['ar' => (string)$value, 'en' => (string)$value], JSON_UNESCAPED_UNICODE);
    }

    private function asJsonDesc($value): ?string
    {
        if ($value === null) return null;

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $value;
            }
        }

        return json_encode(['ar' => (string)$value, 'en' => (string)$value], JSON_UNESCAPED_UNICODE);
    }
}