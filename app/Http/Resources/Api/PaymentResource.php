<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $invoice = $this->relationLoaded('invoice') ? $this->invoice : null;

        return [
            'id' => $this->id,

            'amount' => (string) $this->amount,
            'currency' => $this->currency,
            

            'method' => $this->method,   // wallet / credit_card / apple_pay...
            'method_label' => __('payment_methods.' . $this->method),
            'status' => $this->status,   // pending / paid / failed...

            'provider' => $this->provider,
            'provider_payment_id' => $this->provider_payment_id,

            'paid_at' => $this->paid_at?->toDateTimeString(),
            'created_at' => $this->created_at?->toDateTimeString(),

            'purpose' => $this->payable_type,
            'purpose_label' => __('payment_purposes.' . $this->payable_type),

            'gateway_transaction_url' => $this->gateway_transaction_url,

            // مختصر للفواتير (اختياري لواجهة "مدفوعاتي")
            'invoice' => $invoice ? [
                'id' => $invoice->id,
                'number' => $invoice->number,
                'type' => $invoice->type,
                'status' => $invoice->status,
                'status_label' => __('invoice_statuses.' . $invoice->status),
                'total' => (string) $invoice->total,
            ] : null,
        ];
    }
}