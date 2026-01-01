<?php

namespace App\Services;

use App\Models\Zone;

class ZoneResolverService
{
    public function resolveId(float $lat, float $lng): ?int
    {
        $zones = Zone::query()
            ->active()
            ->whereNotNull('min_lat')->whereNotNull('max_lat')
            ->whereNotNull('min_lng')->whereNotNull('max_lng')
            ->where('min_lat', '<=', $lat)->where('max_lat', '>=', $lat)
            ->where('min_lng', '<=', $lng)->where('max_lng', '>=', $lng)
            ->orderBy('sort_order')
            ->get(['id', 'polygon']);

        foreach ($zones as $zone) {
            $poly = $zone->polygon ?? [];
            if ($this->pointInPolygon($lat, $lng, $poly)) {
                return $zone->id;
            }
        }

        return null;
    }

    private function pointInPolygon(float $lat, float $lng, array $polygon): bool
    {
        // Ray casting (polygon = [['lat'=>..,'lng'=>..], ...])
        $inside = false;
        $n = count($polygon);
        if ($n < 3) return false;

        for ($i = 0, $j = $n - 1; $i < $n; $j = $i++) {
            $xi = (float)($polygon[$i]['lng'] ?? 0);
            $yi = (float)($polygon[$i]['lat'] ?? 0);
            $xj = (float)($polygon[$j]['lng'] ?? 0);
            $yj = (float)($polygon[$j]['lat'] ?? 0);

            $intersect = (($yi > $lat) !== ($yj > $lat))
                && ($lng < ($xj - $xi) * ($lat - $yi) / (($yj - $yi) ?: 1e-12) + $xi);

            if ($intersect) $inside = !$inside;
        }

        return $inside;
    }
}