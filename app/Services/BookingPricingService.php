<?php

namespace App\Services;

use App\Models\Address;
use App\Models\Service;
use App\Models\User;

class BookingPricingService
{
    public function __construct(
        private PricingService $pricingService,
        private ZoneResolverService $zoneResolver,
    ) {}

    public function resolve(Service $service, ?User $user, Address $address, string $time): array
    {
        $zoneId = $this->zoneResolver->resolveId((float) $address->lat, (float) $address->lng);

        $timePeriod = $this->resolveTimePeriodFromTime($time);

        $resolved = $this->pricingService->resolveServicePrice(
            $service,
            $user,
            $zoneId ?: null,
            $timePeriod
        );

        return [
            'zone_id' => $zoneId ?: null,
            'time_period' => in_array($timePeriod, ['morning','evening']) ? $timePeriod : 'all',

            'unit_price' => $resolved['unit_price'],
            'discounted_price' => $resolved['discounted_price'],
            'final_unit_price' => $resolved['final_unit_price'],

            'pricing_source' => $resolved['pricing_source'], // base|zone|group
            'applied_id' => $resolved['applied_id'],
        ];
    }

    private function resolveTimePeriodFromTime(string $time): string
    {
        try {
            $h = (int) explode(':', $time)[0];
            return $h < 15 ? 'morning' : 'evening';
        } catch (\Throwable $e) {
            return 'all';
        }
    }
}