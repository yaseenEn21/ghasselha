<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\WalletResource;
use App\Models\Wallet;
use App\Services\WalletService;
use Illuminate\Http\Request;
use App\Http\Requests\Api\WalletTopupRequest;
use App\Models\Payment;
use App\Services\MoyasarService;
use Illuminate\Support\Facades\DB;

class WalletController extends Controller
{
    public function show(Request $request, WalletService $walletService)
    {
        $user = $request->user();
        if (!$user)
            return api_error('Unauthenticated', 401);

        $wallet = $walletService->getOrCreateWallet($user);

        return api_success(new WalletResource($wallet));
    }

    public function store(WalletTopupRequest $request, MoyasarService $moyasar)
    {
        $user = $request->user();
        if (!$user)
            return api_error('Unauthenticated', 401);

        $amount = (float) $request->input('amount');
        $currency = config('services.moyasar.currency', 'SAR');

        // Moyasar expects smallest unit integer (SAR halalas) :contentReference[oaicite:4]{index=4}
        $amountHalala = (int) round($amount * 100);

        try {
            $payment = DB::transaction(function () use ($user, $amount, $currency) {
                return Payment::create([
                    'user_id' => $user->id,
                    'invoice_id' => null,
                    'payable_type' => 'wallet_topup',
                    'payable_id' => null,

                    'amount' => $amount,
                    'currency' => $currency,

                    'method' => 'credit_card',
                    'status' => 'pending',

                    'gateway' => 'moyasar',
                ]);
            });

            $invoice = $moyasar->createInvoice([
                'amount' => $amountHalala,
                'currency' => $currency,
                'description' => "Wallet topup #{$payment->id}",
                'callback_url' => config('services.moyasar.callback_url'),
                'success_url' => config('services.moyasar.success_url'),
                'back_url' => config('services.moyasar.back_url'),
                // مهم جداً للربط لاحقاً في الويبهوك
                'metadata' => [
                    'local_payment_id' => (string) $payment->id,
                    'user_id' => (string) $user->id,
                    'purpose' => 'wallet_topup',
                ],
            ]);

            $payment->update([
                'gateway_invoice_id' => $invoice['id'] ?? null,
                'gateway_transaction_url' => $invoice['url'] ?? null,
                'gateway_status' => $invoice['status'] ?? null,
                'gateway_raw' => $invoice,
            ]);

            return api_success([
                'payment_id' => $payment->id,
                'checkout_url' => $payment->gateway_transaction_url,
                'gateway_invoice_id' => $payment->gateway_invoice_id,
            ], 'Checkout URL created', 201);

        } catch (\Throwable $e) {
            return api_error('Payment initialization failed', 500, ['error' => $e->getMessage()]);
        }
    }
}
