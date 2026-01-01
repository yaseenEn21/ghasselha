<?php

namespace Database\Seeders;

use App\Models\Package;
use App\Models\PackageSubscription;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PackageSubscriptionSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            $actorId = User::query()->where('user_type', 'admin')->value('id')
                ?? User::query()->value('id');

            // احضر مستخدمين للتجربة (يفضل id 1,2)
            $u1 = User::query()->find(4) ?? User::query()->orderBy('id')->first();
            $u2 = User::query()->find(5) ?? User::query()->orderBy('id')->skip(1)->first();

            if (!$u1 || !$u2) {
                return;
            }

            $pkg5  = Package::query()->where('name', 'باقة 5 غسلات')->first();
            $pkg10 = Package::query()->where('name', 'باقة 10 غسلات')->first();
            $pkg2  = Package::query()->where('name', 'باقة تجربة 2 غسلة')->first();

            if (!$pkg5 || !$pkg10 || !$pkg2) {
                return;
            }

            // (اختياري) تنظيف اشتراكات قديمة لنفس المستخدم/الباقة حتى ما تتكرر بالاختبار
            PackageSubscription::query()
                ->whereIn('user_id', [$u1->id, $u2->id])
                ->whereIn('package_id', [$pkg5->id, $pkg10->id, $pkg2->id])
                ->forceDelete();

            // user1 => pkg5 active
            $final5 = $pkg5->discounted_price ?? $pkg5->price;
            PackageSubscription::create([
                'user_id' => $u1->id,
                'package_id' => $pkg5->id,
                'starts_at' => now(),
                'ends_at' => now()->addDays($pkg5->validity_days),
                'status' => 'active',

                'price_snapshot' => $pkg5->price,
                'discounted_price_snapshot' => $pkg5->discounted_price,
                'final_price_snapshot' => $final5,

                'total_washes_snapshot' => $pkg5->washes_count,
                'remaining_washes' => 3, // للتجربة (أقل من الإجمالي)

                'purchased_at' => now(),
                'created_by' => $actorId,
                'updated_by' => $actorId,
            ]);

            // user2 => pkg2 active
            $final2 = $pkg2->discounted_price ?? $pkg2->price;
            PackageSubscription::create([
                'user_id' => $u2->id,
                'package_id' => $pkg2->id,
                'starts_at' => now(),
                'ends_at' => now()->addDays($pkg2->validity_days),
                'status' => 'active',

                'price_snapshot' => $pkg2->price,
                'discounted_price_snapshot' => $pkg2->discounted_price,
                'final_price_snapshot' => $final2,

                'total_washes_snapshot' => $pkg2->washes_count,
                'remaining_washes' => 1,

                'purchased_at' => now(),
                'created_by' => $actorId,
                'updated_by' => $actorId,
            ]);

            // user1 => pkg10 expired (للاختبار)
            $final10 = $pkg10->discounted_price ?? $pkg10->price;
            PackageSubscription::create([
                'user_id' => $u1->id,
                'package_id' => $pkg10->id,
                'starts_at' => now()->subDays(90),
                'ends_at' => now()->subDays(10),
                'status' => 'expired',

                'price_snapshot' => $pkg10->price,
                'discounted_price_snapshot' => $pkg10->discounted_price,
                'final_price_snapshot' => $final10,

                'total_washes_snapshot' => $pkg10->washes_count,
                'remaining_washes' => 0,

                'purchased_at' => now()->subDays(90),
                'created_by' => $actorId,
                'updated_by' => $actorId,
            ]);
        });
    }
}