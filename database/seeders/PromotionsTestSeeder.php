<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PromotionsTestSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $today = now()->toDateString();

        DB::transaction(function () use ($now, $today) {

            $actorId = DB::table('users')->where('user_type', 'admin')->value('id')
                ?? DB::table('users')->orderBy('id')->value('id');

            $services = DB::table('services')->orderBy('id')->limit(2)->pluck('id')->toArray();
            $packages = DB::table('packages')->orderBy('id')->limit(2)->pluck('id')->toArray();

            // 1) Promotion: 10% off selected services
            $promo1Id = DB::table('promotions')->insertGetId([
                'name' => json_encode(['ar' => 'خصم 10% على خدمات محددة', 'en' => '10% off selected services'], JSON_UNESCAPED_UNICODE),
                'description' => json_encode(['ar' => 'خصم لفترة محدودة على خدمات معينة.', 'en' => 'Limited-time discount on selected services.'], JSON_UNESCAPED_UNICODE),
                'applies_to' => 'service',
                'apply_all_services' => false,
                'apply_all_packages' => false,
                'discount_type' => 'percent',
                'discount_value' => 10,
                'max_discount' => 50,
                'starts_at' => $today,
                'ends_at' => null,
                'is_active' => true,
                'created_by' => $actorId,
                'updated_by' => $actorId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            foreach ($services as $sid) {
                DB::table('promotion_services')->updateOrInsert(
                    ['promotion_id' => $promo1Id, 'service_id' => $sid],
                    ['is_active' => true, 'created_at' => $now, 'updated_at' => $now]
                );
            }

            DB::table('promotion_coupons')->insert([
                'promotion_id' => $promo1Id,
                'code' => 'SERV10',
                'is_active' => true,
                'usage_limit_total' => 100,
                'usage_limit_per_user' => 2,
                'used_count' => 0,
                'min_invoice_total' => 50,
                'max_discount' => null,
                'starts_at' => $today,
                'ends_at' => null,
                'meta' => json_encode(['note' => 'Test coupon for services'], JSON_UNESCAPED_UNICODE),
                'created_by' => $actorId,
                'updated_by' => $actorId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // 2) Promotion: 50 SAR off selected packages
            $promo2Id = DB::table('promotions')->insertGetId([
                'name' => json_encode(['ar' => 'خصم 50 ريال على باقات محددة', 'en' => 'SAR 50 off selected packages'], JSON_UNESCAPED_UNICODE),
                'description' => json_encode(['ar' => 'خصم ثابت على باقات معينة.', 'en' => 'Fixed discount on selected packages.'], JSON_UNESCAPED_UNICODE),
                'applies_to' => 'package',
                'apply_all_services' => false,
                'apply_all_packages' => false,
                'discount_type' => 'fixed',
                'discount_value' => 50,
                'max_discount' => null,
                'starts_at' => $today,
                'ends_at' => null,
                'is_active' => true,
                'created_by' => $actorId,
                'updated_by' => $actorId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            foreach ($packages as $pid) {
                DB::table('promotion_packages')->updateOrInsert(
                    ['promotion_id' => $promo2Id, 'package_id' => $pid],
                    ['is_active' => true, 'created_at' => $now, 'updated_at' => $now]
                );
            }

            DB::table('promotion_coupons')->insert([
                'promotion_id' => $promo2Id,
                'code' => 'PACK50',
                'is_active' => true,
                'usage_limit_total' => 50,
                'usage_limit_per_user' => 1,
                'used_count' => 0,
                'min_invoice_total' => 100,
                'max_discount' => null,
                'starts_at' => $today,
                'ends_at' => null,
                'meta' => json_encode(['note' => 'Test coupon for packages'], JSON_UNESCAPED_UNICODE),
                'created_by' => $actorId,
                'updated_by' => $actorId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // 3) Promotion: BOTH (service + package) 15% off (with max 60)
            $promo3Id = DB::table('promotions')->insertGetId([
                'name' => json_encode(['ar' => 'خصم 15% على خدمة + باقة', 'en' => '15% off service + package'], JSON_UNESCAPED_UNICODE),
                'description' => json_encode(['ar' => 'خصم عند شراء عناصر محددة.', 'en' => 'Discount when invoice contains specific items.'], JSON_UNESCAPED_UNICODE),
                'applies_to' => 'both',
                'apply_all_services' => false,
                'apply_all_packages' => false,
                'discount_type' => 'percent',
                'discount_value' => 15,
                'max_discount' => 60,
                'starts_at' => $today,
                'ends_at' => null,
                'is_active' => true,
                'created_by' => $actorId,
                'updated_by' => $actorId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            if (!empty($services)) {
                DB::table('promotion_services')->updateOrInsert(
                    ['promotion_id' => $promo3Id, 'service_id' => $services[0]],
                    ['is_active' => true, 'created_at' => $now, 'updated_at' => $now]
                );
            }

            if (!empty($packages)) {
                DB::table('promotion_packages')->updateOrInsert(
                    ['promotion_id' => $promo3Id, 'package_id' => $packages[0]],
                    ['is_active' => true, 'created_at' => $now, 'updated_at' => $now]
                );
            }

            DB::table('promotion_coupons')->insert([
                'promotion_id' => $promo3Id,
                'code' => 'BOTH15',
                'is_active' => true,
                'usage_limit_total' => null,
                'usage_limit_per_user' => null,
                'used_count' => 0,
                'min_invoice_total' => null,
                'max_discount' => null,
                'starts_at' => $today,
                'ends_at' => null,
                'meta' => json_encode(['note' => 'Test coupon for both'], JSON_UNESCAPED_UNICODE),
                'created_by' => $actorId,
                'updated_by' => $actorId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        });
    }
}