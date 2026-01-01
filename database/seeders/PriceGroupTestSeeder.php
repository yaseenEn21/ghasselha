<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PriceGroupTestSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        DB::transaction(function () use ($now) {

            // مستخدم "فاعل" نسند له created_by/updated_by (أدمن إن وجد)
            $actorId = DB::table('users')->where('user_type', 'admin')->value('id')
                ?? DB::table('users')->orderBy('id')->value('id');

            // 1) Customer Groups
            DB::table('customer_groups')->updateOrInsert(
                ['name' => 'Regular'],
                [
                    'is_active' => true,
                    'created_by' => $actorId,
                    'updated_by' => $actorId,
                    'deleted_at' => null,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );

            DB::table('customer_groups')->updateOrInsert(
                ['name' => 'VIP'],
                [
                    'is_active' => true,
                    'created_by' => $actorId,
                    'updated_by' => $actorId,
                    'deleted_at' => null,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );

            $regularId = DB::table('customer_groups')->where('name', 'Regular')->value('id');
            $vipId     = DB::table('customer_groups')->where('name', 'VIP')->value('id');

            // 2) Assign users (حسب طلبك: id 1,2)
            DB::table('users')->where('id', 1)->update([
                'customer_group_id' => $vipId,
                'updated_at' => $now,
            ]);

            DB::table('users')->where('id', 2)->update([
                'customer_group_id' => $regularId,
                'updated_at' => $now,
            ]);

            // 3) Create VIP override prices for first 2 services
            $services = DB::table('services')
                ->select('id', 'price', 'discounted_price')
                ->orderBy('id')
                ->limit(2)
                ->get();

            foreach ($services as $svc) {
                $basePrice = (float) $svc->price;

                // خصم تجريبي: -5 (ولا يقل عن 0)
                $vipPrice = max(0, $basePrice - 5);

                // خصم إضافي بسيط إن حبيت
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