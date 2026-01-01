<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // paid_amount ما نخزنها الآن، نحسبها من المدفوعات (مبدئيًا)
        $paidAmount = $this->relationLoaded('payments')
            ? (float) $this->payments->where('status', 'paid')->sum('amount')
            : 0.0;

        $remaining = max(0, (float)$this->total - $paidAmount);

        return [
            'id' => $this->id,
            'number' => $this->number,

            'type' => $this->type, // invoice/adjustment/credit_note
            'status' => $this->status,
            'status_label' => __('invoice_statuses.' . $this->status),
            'version' => (int) $this->version,
            'parent_invoice_id' => $this->parent_invoice_id,

            'subtotal' => (string) $this->subtotal,
            'discount' => (string) $this->discount,
            'tax' => (string) $this->tax,
            'total' => (string) $this->total,

            'paid_amount' => (string) number_format($paidAmount, 2, '.', ''),
            'remaining_amount' => (string) number_format($remaining, 2, '.', ''),

            'currency' => $this->currency,
            'issued_at' => $this->issued_at?->toDateTimeString(),
            'paid_at' => $this->paid_at?->toDateTimeString(),

            'items' => $this->relationLoaded('items')
                ? $this->items->sortBy('sort_order')->map(fn($i) => new InvoiceItemResource($i))->values()
                : [],
            
            // 'payments' => $this->payments
        ];
    }
}