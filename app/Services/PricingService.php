<?php

namespace App\Services;

use App\Models\Service;
use App\Models\ServiceGroupPrice;
use App\Models\ServiceZonePrice;
use App\Models\User;

class PricingService
{
    public function resolveServicePrice(Service $service, ?User $user, ?int $zoneId, ?string $timePeriod): array
    {
        // 1) GROUP
        if ($user?->customer_group_id) {
            $group = ServiceGroupPrice::query()
                ->where('service_id', $service->id)
                ->where('customer_group_id', $user->customer_group_id)
                ->where('is_active', true)
                ->first();

            if ($group) {
                $price = (float) $group->price;
                $disc = $group->discounted_price !== null ? (float) $group->discounted_price : null;

                return [
                    'unit_price' => $price,
                    'discounted_price' => $disc,
                    'final_unit_price' => $disc ?? $price,
                    'pricing_source' => 'group',
                    'applied_id' => $group->id,
                ];
            }
        }

        // 2) ZONE + TIME
        if ($zoneId) {
            $tp = in_array($timePeriod, ['morning', 'evening']) ? $timePeriod : 'all';

            $zonePrice = ServiceZonePrice::query()
                ->where('service_id', $service->id)
                ->where('zone_id', $zoneId)
                ->where('is_active', true)
                ->whereIn('time_period', [$tp, 'all']) // ✅
                ->orderByRaw("CASE WHEN time_period = ? THEN 0 ELSE 1 END", [$tp]) // يقدّم المطابق
                ->first();

            if ($zonePrice) {
                $price = (float) $zonePrice->price;
                $disc = $zonePrice->discounted_price !== null ? (float) $zonePrice->discounted_price : null;

                return [
                    'unit_price' => $price,
                    'discounted_price' => $disc,
                    'final_unit_price' => $disc ?? $price,
                    'pricing_source' => 'zone',
                    'applied_id' => $zonePrice->id,
                ];
            }
        }

        // 3) BASE
        $basePrice = (float) $service->price;
        $baseDisc = $service->discounted_price !== null ? (float) $service->discounted_price : null;

        return [
            'unit_price' => $basePrice,
            'discounted_price' => $baseDisc,
            'final_unit_price' => $baseDisc ?? $basePrice,
            'pricing_source' => 'base',
            'applied_id' => null,
        ];
    }

}