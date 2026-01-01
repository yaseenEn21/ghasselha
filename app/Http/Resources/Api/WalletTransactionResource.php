<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WalletTransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $payment_method = $this->relationLoaded('payment') && $this->payment
                ? $this->payment->method
                : null;

        return [
            'id' => $this->id,
            'direction' => $this->direction, // credit/debit
            'type' => $this->type,
            'type_label' => __('transactions.' . $this->type),

            'amount' => (string) $this->amount,
            'balance_before' => (string) $this->balance_before,
            'balance_after' => (string) $this->balance_after,

            'description' => $this->description ? i18n($this->description) : null,

            'method' => $payment_method,
            'method_label' => $payment_method ? __('payment_methods.' . $payment_method) : null
        ];
    }
}
