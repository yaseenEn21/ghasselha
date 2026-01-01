<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\InvoicePaymentRequest;
use App\Http\Resources\Api\PaymentResource;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\MoyasarService;
use App\Services\WalletService;
use Illuminate\Support\Facades\DB;

class InvoicePaymentController extends Controller
{
    /**
     * POST /api/v1/invoices/{invoice}/payments
     * body: { "method": "wallet|credit_card|apple_pay|google_pay" }
     */
    public function store(InvoicePaymentRequest $request, $invoiceId, WalletService $walletService)
    {
        $user = $request->user();
        if (!$user)
            return api_error('Unauthenticated', 401);

        $method = $request->input('method');

        $payment = DB::transaction(function () use ($user, $method, $invoiceId, $walletService) {

            $invoice = Invoice::query()
                ->where('id', $invoiceId)
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->first();

            if (!$invoice) {
                return api_error('Invoice not found', 404);
            }

            if (!in_array($invoice->status, ['unpaid'], true)) {
                return api_error('Invoice is not payable', 409);
            }

            // احسب المتبقي
            $paidAmount = (float) Payment::query()
                ->where('invoice_id', $invoice->id)
                ->where('status', 'paid')
                ->sum('amount');

            $remaining = max(0, (float) $invoice->total - $paidAmount);

            if ($remaining <= 0.0) {
                return api_error('Invoice already paid', 409);
            }

            // ✅ إنشاء Payment
            $p = Payment::create([
                'user_id' => $user->id,
                'invoice_id' => $invoice->id,

                'amount' => $remaining,
                'currency' => $invoice->currency,

                'method' => $method,
                'status' => $method === 'wallet' ? 'paid' : 'pending',
                'paid_at' => $method === 'wallet' ? now() : null,

                // payable اختياري (نحط invoiceable لو موجود)
                'payable_type' => $invoice->resolvePayableType(),
                'payable_id' => $invoice->resolvePayableId(),

                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]);

            // ✅ لو Wallet → خصم + إقفال الفاتورة
            if ($method === 'wallet') {
                $walletService->debit(
                    $user,
                    $remaining,
                    'booking_charge', // لاحقاً بنقرر حسب invoiceable (حجز/باقة/متجر)
                    ['ar' => 'سداد فاتورة', 'en' => 'Invoice payment'],
                    $invoice->invoiceable, // reference
                    $p->id,
                    $user->id,
                    ['invoice_id' => $invoice->id, 'invoice_number' => $invoice->number]
                );

                // حدّث الفاتورة
                $invoice->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                    'is_locked' => true,
                    'updated_by' => $user->id,
                ]);

                if (data_get($invoice->meta, 'purpose') === 'package_purchase') {
                    app(\App\Services\PackagePurchaseService::class)
                        ->fulfillPaidInvoice($invoice->fresh(), $user->id);
                }

                if (data_get($invoice->meta, 'purpose') === 'booking') {
                    app(\App\Services\BookingFulfillmentService::class)
                        ->fulfillPaidInvoice($invoice->fresh(), $user->id);
                }

            } else {
                // بوابة الدفع (Moyasar) -> رجّع رابط الدفع
                $p->update([
                    'gateway' => 'moyasar',
                    'meta' => array_merge($p->meta ?? [], [
                        'purpose' => data_get($invoice->meta, 'purpose'),
                        'invoice_number' => $invoice->number,
                    ]),
                ]);

                // create moyasar invoice url
                $amountHalala = (int) round($remaining * 100);

                $moyasarInvoice = app(MoyasarService::class)->createInvoice([
                    'amount' => $amountHalala,
                    'currency' => $invoice->currency,
                    'description' => "Invoice {$invoice->number}",
                    'callback_url' => config('services.moyasar.callback_url'),
                    'success_url' => config('services.moyasar.success_url'),
                    'back_url' => config('services.moyasar.back_url'),
                    'metadata' => [
                        'local_payment_id' => (string) $p->id,
                        'invoice_id' => (string) $invoice->id,
                        'user_id' => (string) $user->id,
                        'purpose' => data_get($invoice->meta, 'purpose'),
                    ],
                ]);

                $p->update([
                    'gateway_invoice_id' => $moyasarInvoice['id'] ?? null,
                    'gateway_transaction_url' => $moyasarInvoice['url'] ?? null,
                    'gateway_status' => $moyasarInvoice['status'] ?? null,
                    'gateway_raw' => $moyasarInvoice,
                ]);
            }

            return $p->load(['invoice:id,number,type,status,total']);
        });

        // لو رجعت api_error من جوة transaction
        if ($payment instanceof \Illuminate\Http\JsonResponse) {
            return $payment;
        }

        return api_success(new PaymentResource($payment), 'Payment created', 201);
    }
}
