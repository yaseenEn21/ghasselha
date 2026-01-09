<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = $request->user('sanctum');
        $groupId = $user?->customer_group_id;

        $zoneId = $request->attributes->get('zone_id');
        $timePeriod = $request->attributes->get('time_period'); // morning|evening

        // Base
        $price = (float) $this->price;
        $discounted = $this->discounted_price !== null ? (float) $this->discounted_price : null;
        $source = 'base';

        // 1) Group
        if ($groupId && $this->relationLoaded('groupPrices')) {
            $override = $this->groupPrices->first();
            if ($override) {
                $price = (float) $override->price;
                $discounted = $override->discounted_price !== null ? (float) $override->discounted_price : null;
                $source = 'group';
            }
        }

        // 2) Zone (فقط إذا ما في group override)
        if ($source === 'base' && $zoneId && $this->relationLoaded('zonePrices')) {
            $tp = in_array($timePeriod, ['morning', 'evening']) ? $timePeriod : 'all';

            $zp = $this->zonePrices->firstWhere('time_period', $tp)
                ?? $this->zonePrices->firstWhere('time_period', 'all');

            if ($zp) {
                $price = (float) $zp->price;
                $discounted = $zp->discounted_price !== null ? (float) $zp->discounted_price : null;
                $source = 'zone';
            }
        }

        return [
            'id' => $this->id,
            'service_category_id' => $this->service_category_id,

            'name' => i18n($this->name),
            'description' => i18n($this->description),

            'rate' => $this->rating_count > 0
                ? number_format((float) $this->rating_avg, 1, '.', '')
                : null,

            'rate_count' => (int) $this->rating_count,


            'duration_minutes' => $this->duration_minutes,

            'price' => number_format($price, 2, '.', ''),
            'discounted_price' => $discounted !== null ? number_format($discounted, 2, '.', '') : null,

            // (اختياري للتأكد أثناء التطوير)
            'pricing_source' => $source,

            // 'image_url' => $this->getFirstMediaUrl('image') ?: defaultImage(),
            'image_url' => $this->getImageUrl(app()->getLocale()) ?: defaultImage(),
        ];
    }

}