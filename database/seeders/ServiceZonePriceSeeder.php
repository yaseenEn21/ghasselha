<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceZonePriceSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        DB::transaction(function () use ($now) {

            $actorId = DB::table('users')->where('user_type', 'admin')->value('id')
                ?? DB::table('users')->orderBy('id')->value('id');

            $riyadhZoneId = DB::table('zones')->where('name', 'الرياض - العليا')->value('id');
            $jeddahZoneId = DB::table('zones')->where('name', 'جدة - الروضة')->value('id');

            if (!$riyadhZoneId || !$jeddahZoneId) {
                return;
            }

            // خذ أول 3 خدمات فعّالة للاختبار
            $services = DB::table('services')
                ->select('id', 'price', 'discounted_price')
                ->where('is_active', true)
                ->orderBy('id')
                ->limit(3)
                ->get();

            if ($services->isEmpty()) {
                return;
            }

            // Helper
            $upsert = function (int $serviceId, int $zoneId, string $timePeriod, float $price, ?float $discounted) use ($actorId, $now) {
                DB::table('service_zone_prices')->updateOrInsert(
                    [
                        'service_id' => $serviceId,
                        'zone_id' => $zoneId,
                        'time_period' => $timePeriod, // all|morning|evening
                    ],
                    [
                        'price' => $price,
                        'discounted_price' => $discounted,
                        'is_active' => true,
                        'created_by' => $actorId,
                        'updated_by' => $actorId,
                        'deleted_at' => null,
                        'updated_at' => $now,
                        'created_at' => $now,
                    ]
                );
            };

            // --- الرياض - العليا ---
            // الخدمة 1: سعر ثابت طوال اليوم (مثال واضح)
            $svc1 = $services[0] ?? null;
            if ($svc1) {
                $upsert((int)$svc1->id, $riyadhZoneId, 'all', 200.00, 180.00);
            }

            // الخدمة 2: صباحي/مسائي (time-based داخل نفس المنطقة)
            $svc2 = $services[1] ?? null;
            if ($svc2) {
                $upsert((int)$svc2->id, $riyadhZoneId, 'morning', 160.00, 150.00);
                $upsert((int)$svc2->id, $riyadhZoneId, 'evening', 180.00, 170.00);
            }

            // الخدمة 3: override زيادة على السعر الأساسي
            $svc3 = $services[2] ?? null;
            if ($svc3) {
                $base = (float)$svc3->price;
                $upsert((int)$svc3->id, $riyadhZoneId, 'all', $base + 30, null);
            }

            // --- جدة - الروضة ---
            // الخدمة 1: سعر مختلف عن الرياض طوال اليوم
            if ($svc1) {
                $upsert((int)$svc1->id, $jeddahZoneId, 'all', 190.00, 175.00);
            }

            // الخدمة 2: سعر طوال اليوم (بدون تفريق صباحي/مسائي)
            if ($svc2) {
                $base = (float)$svc2->price;
                $disc = $svc2->discounted_price !== null ? (float)$svc2->discounted_price : null;

                $upsert((int)$svc2->id, $jeddahZoneId, 'all', $base + 20, $disc ? $disc + 15 : null);
            }
        });
    }
}
