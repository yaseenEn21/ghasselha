<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PackageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // Controller سيحمّل subscriptions للمستخدم الحالي فقط (active) إن كان مسجل
        $sub = $this->relationLoaded('subscriptions') ? $this->subscriptions->first() : null;

        return [
            'id' => $this->id,
            'name' => i18n($this->name),
            'description' => i18n($this->description),
            
            'short_description' => i18n($this->description),
            
            'price' => (string) $this->price,
            'discounted_price' => $this->discounted_price !== null ? (string) $this->discounted_price : null,

            'washes_count' => $this->washes_count,
            
            'remaining_washes' => $sub?->remaining_washes,
            'total_washes_snapshot' => $sub?->total_washes_snapshot,

            'validity_days' => $this->validity_days,

            'image_url' => $this->getFirstMediaUrl('image') ?: defaultImage(),

            // مهم للـ Home: هل هو مشترك وفعّال؟
            'is_subscribed_active' => $sub ? (bool) $sub->is_currently_active : false,
            'subscription_ends_at' => $sub?->ends_at?->toISOString(),

            // تفاصيل الخدمات داخل الباقة (فقط عند show أو with_services=1)
            // 'services' => $this->whenLoaded('services', function () use ($request) {
            //     return PackageIncludedServiceResource::collection($this->services);
            // }),
        ];
    }
}
