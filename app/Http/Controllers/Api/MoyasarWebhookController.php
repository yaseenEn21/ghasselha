<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Services\PackagePurchaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MoyasarWebhookController extends Controller
{
    public function handle(Request $request, PackagePurchaseService $svc)
    {
        $secret = config('services.moyasar.webhook_secret');

        // Moyasar webhook payload includes secret_token :contentReference[oaicite:6]{index=6}
        // if ($secret && $request->input('secret_token') !== $secret) {
        //     \Log::error('Invalid Moyasar webhook token');
        //     return response()->json(['success' => false, 'message' => 'Invalid webhook token'], 403);
        // }

        $type = $request->input('type'); // e.g payment_paid
        $data = $request->input('data', []);

        if (!is_array($data)) {
            return response()->json(['success' => false, 'message' => 'Invalid payload'], 400);
        }

        // نحن أنشأنا invoice، فالدفع الحقيقي يأتي لاحقاً وفيه invoice_id
        $gatewayPaymentId = $data['id'] ?? null;
        $gatewayInvoiceId = $data['invoice_id'] ?? null;

        // أفضل ربط: metadata.local_payment_id
        $localPaymentId = $data['metadata']['local_payment_id'] ?? null;
        
        /** @var Payment|null $payment */
        $payment = null;

        if ($localPaymentId) {
            $payment = Payment::query()->where('id', (int) $localPaymentId)->where('gateway', 'moyasar')->first();
        }

        if (!$payment && $gatewayInvoiceId) {
            $payment = Payment::query()
                ->where('gateway', 'moyasar')
                ->where('gateway_invoice_id', $gatewayInvoiceId)
                ->first();
        }

        if (!$payment) {
            // مش خطأ قاتل: ممكن webhook لحاجة مش من نظامنا
            return response()->json(['success' => true, 'message' => 'Ignored'], 200);
        }

        // منع التكرار
        if (in_array($payment->status, ['paid', 'refunded', 'cancelled'], true)) {
            return response()->json(['success' => true, 'message' => 'Already processed'], 200);
        }

        if ($type === 'payment_paid') {
            DB::transaction(function () use ($payment, $data, $gatewayPaymentId, $svc) {

                $amountPaid = ((int) ($data['amount'] ?? 0)) / 100; // halalas -> SAR
                
                if ($amountPaid == $payment->amount) {

                    $payment->update([
                        'status' => 'paid',
                        'gateway_payment_id' => $gatewayPaymentId,
                        'gateway_status' => $data['status'] ?? 'paid',
                        'paid_at' => now(),
                        'gateway_raw' => $data,
                    ]);

                    if ($payment->invoice_id) {

                        $invoice = Invoice::query()
                            ->where('id', $payment->invoice_id)
                            ->lockForUpdate()
                            ->first();

                        if ($invoice && $invoice->status !== 'paid') {

                            $paidAmount = (float) Payment::query()
                                ->where('invoice_id', $invoice->id)
                                ->where('status', 'paid')
                                ->sum('amount');

                            $remaining = max(0, (float) $invoice->total - $paidAmount);

                            // ✅ إذا اكتملت الفاتورة
                            if ($remaining <= 0.0) {
                                $invoice->update([
                                    'status' => 'paid',
                                    'paid_at' => now(),
                                    'is_locked' => true,
                                    'updated_by' => $payment->user_id, // أو null
                                ]);

                                // ✅ هنا مكان fulfillPaidInvoice
                                if (data_get($invoice->meta, 'purpose') === 'package_purchase') {
                                    $svc->fulfillPaidInvoice($invoice->fresh(), $payment->user_id);
                                }

                                if (data_get($invoice->meta, 'purpose') === 'booking_invoice') {
                                    app(\App\Services\BookingFulfillmentService::class)
                                        ->fulfillPaidInvoice($invoice->fresh(), $payment->user_id);
                                }
                            }
                        }
                    }

                    // 2) تطبيق الأثر: شحن محفظة
                    if ($payment->payable_type === 'wallet_topup') {

                        $wallet = Wallet::query()->where('user_id', $payment->user_id)->lockForUpdate()->first();
                        if (!$wallet) {
                            $wallet = Wallet::create([
                                'user_id' => $payment->user_id,
                                'balance' => 0,
                            ]);
                            $wallet->refresh();
                        }

                        $before = (float) $wallet->balance;
                        $after = $before + (float) $amountPaid;

                        $wallet->update(['balance' => $after]);

                        WalletTransaction::create([
                            'wallet_id' => $wallet->id,
                            'user_id' => $payment->user_id,

                            'direction' => 'credit',
                            'type' => 'topup',

                            'amount' => $amountPaid,
                            'balance_before' => $before,
                            'balance_after' => $after,

                            'description' => [
                                'ar' => 'شحن محفظة',
                                'en' => 'Wallet topup',
                            ],
                            'meta' => [
                                'gateway' => 'moyasar',
                                'gateway_payment_id' => $gatewayPaymentId,
                                'gateway_invoice_id' => $payment->gateway_invoice_id,
                            ],

                            'payment_id' => $payment->id,
                            'created_by' => $payment->user_id,
                            'updated_by' => $payment->user_id,
                        ]);
                    }

                } else {
                    $payment->update([
                        'status' => 'failed',
                        'gateway_payment_id' => $gatewayPaymentId,
                        'gateway_status' => $data['status'] ?? 'failed',
                        'gateway_raw' => $data,
                    ]);
                }

            });

            return response()->json(['success' => true], 200);
        }

        if (in_array($type, ['payment_failed', 'payment_voided', 'payment_canceled'], true)) {
            $payment->update([
                'status' => 'failed',
                'gateway_payment_id' => $gatewayPaymentId,
                'gateway_status' => $data['status'] ?? 'failed',
                'gateway_raw' => $data,
            ]);

            return response()->json(['success' => true], 200);
        }

        return response()->json(['success' => true, 'message' => 'Unhandled event'], 200);
    }

    public function callback(Request $request)
    {
        return view('payment.callback');
    }

    public function success(Request $request)
    {
        return view('payment.success');
    }
}