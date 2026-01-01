<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $latestUnpaid = $this->relationLoaded('invoices')
            ? $this->invoices->where('status', 'unpaid')->sortByDesc('id')->first()
            : null;

        return [
            'id' => (int) $this->id,
            'status' => (string) $this->status,
            'status_label' => __('bookings.status.'.$this->status),

            'date' => $this->booking_date?->format('d-m-Y'),
            'start_time' => substr((string)$this->start_time, 0, 5),
            'end_time' => substr((string)$this->end_time, 0, 5),
            'duration_minutes' => (int) $this->duration_minutes,

            'car_id' => (int) $this->car_id,
            'address_id' => (int) $this->address_id,
            'service_id' => (int) $this->service_id,
            'employee_id' => $this->employee_id ? (int) $this->employee_id : null,

            'service_name' => $this->relationLoaded('service') ? i18n($this->service->name) : null,

            'package_subscription_id' => $this->package_subscription_id ? (int) $this->package_subscription_id : null,
            'package_covers_service' => (bool)($this->meta['package_covers_service'] ?? false),
            'package_deducted' => (bool)($this->meta['package_deducted'] ?? false),

            'totals' => [
                'service_final' => (float) $this->service_final_price_snapshot,
                'products_subtotal' => (float) $this->products_subtotal_snapshot,
                'subtotal' => (float) $this->subtotal_snapshot,
                'discount' => (float) $this->discount_snapshot,
                'tax' => (float) $this->tax_snapshot,
                'total' => (float) $this->total_snapshot,
                'currency' => (string) $this->currency,
            ],

            'products' => BookingProductResource::collection($this->whenLoaded('products')),

            'latest_unpaid_invoice' => $latestUnpaid ? [
                'id' => (int) $latestUnpaid->id,
                'number' => (string) $latestUnpaid->number,
                'status' => (string) $latestUnpaid->status,
                'total' => (float) $latestUnpaid->total,
                'currency' => (string) $latestUnpaid->currency,
            ] : null,
        ];
    }
}
