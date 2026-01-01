<?php

namespace Database\Seeders;

use App\Models\Zone;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ZoneSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        DB::transaction(function () use ($now) {

            $actorId = DB::table('users')->where('user_type', 'admin')->value('id')
                ?? DB::table('users')->orderBy('id')->value('id');

            $zones = [
                [
                    'name' => 'الرياض - العليا',
                    // مستطيل تقريبي (اختبار)
                    'polygon' => [
                        ['lat' => 24.6980, 'lng' => 46.6610],
                        ['lat' => 24.6980, 'lng' => 46.6850],
                        ['lat' => 24.7240, 'lng' => 46.6850],
                        ['lat' => 24.7240, 'lng' => 46.6610],
                        ['lat' => 24.6980, 'lng' => 46.6610],
                    ],
                    'sort_order' => 1,
                ],
                [
                    'name' => 'جدة - الروضة',
                    // مستطيل تقريبي (اختبار)
                    'polygon' => [
                        ['lat' => 21.5700, 'lng' => 39.1450],
                        ['lat' => 21.5700, 'lng' => 39.1750],
                        ['lat' => 21.6000, 'lng' => 39.1750],
                        ['lat' => 21.6000, 'lng' => 39.1450],
                        ['lat' => 21.5700, 'lng' => 39.1450],
                    ],
                    'sort_order' => 2,
                ],
            ];

            foreach ($zones as $z) {
                $lats = array_map(fn ($p) => (float) $p['lat'], $z['polygon']);
                $lngs = array_map(fn ($p) => (float) $p['lng'], $z['polygon']);

                $minLat = min($lats);
                $maxLat = max($lats);
                $minLng = min($lngs);
                $maxLng = max($lngs);

                $centerLat = ($minLat + $maxLat) / 2;
                $centerLng = ($minLng + $maxLng) / 2;

                $zone = Zone::withTrashed()->updateOrCreate(
                    ['name' => $z['name']],
                    [
                        'polygon' => $z['polygon'],
                        'min_lat' => $minLat,
                        'max_lat' => $maxLat,
                        'min_lng' => $minLng,
                        'max_lng' => $maxLng,
                        'center_lat' => $centerLat,
                        'center_lng' => $centerLng,
                        'sort_order' => $z['sort_order'] ?? 0,
                        'is_active' => true,
                        'created_by' => $actorId,
                        'updated_by' => $actorId,
                        'updated_at' => $now,
                        'created_at' => $now,
                    ]
                );

                if ($zone->trashed()) {
                    $zone->restore();
                }
            }
        });
    }
}