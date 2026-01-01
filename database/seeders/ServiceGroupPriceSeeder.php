<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceGroupPriceSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        DB::transaction(function () use ($now) {

            $actorId = DB::table('users')->where('user_type', 'admin')->value('id')
                ?? DB::table('users')->orderBy('id')->value('id');

            $vipId = DB::table('customer_groups')->where('name', 'VIP')->value('id');

            // لو VIP group مش موجود (مثلاً ما شغلت CustomerGroupSeeder)، اطلع بدون أخطاء
            if (!$vipId) {
                return;
            }

            // Override لأول خدمتين (اختبار)
            $services = DB::table('services')
                ->select('id', 'price', 'discounted_price')
                ->where('is_active', true)
                ->orderBy('id')
                ->limit(2)
                ->get();

            foreach ($services as $svc) {
                $basePrice = (float) $svc->price;

                // خصم تجريبي: -5
                $vipPrice = max(0, $basePrice - 5);

                $vipDiscounted = $svc->discounted_price !== null
                    ? max(0, (float) $svc->discounted_price - 5)
                    : null;

                DB::table('service_group_prices')->updateOrInsert(
                    [
                        'service_id' => $svc->id,
                        'customer_group_id' => $vipId,
                    ],
                    [
                        'price' => $vipPrice,
                        'discounted_price' => $vipDiscounted,
                        'is_active' => true,

                        'created_by' => $actorId,
                        'updated_by' => $actorId,

                        'deleted_at' => null,
                        'updated_at' => $now,
                        'created_at' => $now,
                    ]
                );
            }
        });
    }
}