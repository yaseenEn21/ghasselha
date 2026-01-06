<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PromotionsTestSeeder extends Seeder
{
    public function run(): void
    {
        $now   = now();
        $today = now()->toDateString();

        DB::transaction(function () use ($now, $today) {

            $actorId = DB::table('users')->where('user_type', 'admin')->value('id')
                ?? DB::table('users')->orderBy('id')->value('id');

            // اختياري: نستخدمهم كـ "meta" إذا ما عندك جداول pivot لتحديد عناصر معينة
            $services = DB::table('services')->orderBy('id')->limit(2)->pluck('id')->toArray();
            $packages = DB::table('packages')->orderBy('id')->limit(2)->pluck('id')->toArray();

            // -----------------------------------------
            // 1) Promotion: 10% off selected services (coupon scope = service)
            // -----------------------------------------
            $promo1Name = json_encode(['ar' => 'خصم 10% على خدمات محددة', 'en' => '10% off selected services'], JSON_UNESCAPED_UNICODE);
            $promo1Desc = json_encode(['ar' => 'خصم لفترة محدودة على خدمات معينة.', 'en' => 'Limited-time discount on selected services.'], JSON_UNESCAPED_UNICODE);

            DB::table('promotions')->updateOrInsert(
                ['name' => $promo1Name],
                [
                    'description' => $promo1Desc,
                    'starts_at'   => $today,
                    'ends_at'     => '2027-01-01',
                    'is_active'   => true,
                    'created_by'  => $actorId,
                    'updated_by'  => $actorId,
                    'created_at'  => $now,
                    'updated_at'  => $now,
                    'deleted_at'  => null,
                ]
            );

            $promo1Id = DB::table('promotions')->where('name', $promo1Name)->value('id');

            $applyAllServices1 = empty($services); // إذا ما في خدمات بالسيستم، خليه يطبق على الكل للتجربة

            DB::table('promotion_coupons')->updateOrInsert(
                ['code' => Str::upper('SERV10')],
                [
                    'promotion_id'        => $promo1Id,
                    'code'                => Str::upper('SERV10'),
                    'is_active'           => true,
                    'starts_at'           => $today,
                    'ends_at'             => null,

                    'applies_to'          => 'service',
                    'apply_all_services'  => $applyAllServices1,
                    'apply_all_packages'  => false,

                    'discount_type'       => 'percent',
                    'discount_value'      => 10,

                    'usage_limit_total'   => 100,
                    'usage_limit_per_user'=> 2,
                    'used_count'          => 0,

                    'min_invoice_total'   => 50,
                    'max_discount'        => null,

                    'meta'                => json_encode([
                        'note'     => 'Test coupon for services',
                        'services' => $applyAllServices1 ? [] : $services,
                    ], JSON_UNESCAPED_UNICODE),

                    'created_by'          => $actorId,
                    'updated_by'          => $actorId,
                    'created_at'          => $now,
                    'updated_at'          => $now,
                    'deleted_at'          => null,
                ]
            );

            // -----------------------------------------
            // 2) Promotion: 50 SAR off selected packages (coupon scope = package)
            // -----------------------------------------
            $promo2Name = json_encode(['ar' => 'خصم 50 ريال على باقات محددة', 'en' => 'SAR 50 off selected packages'], JSON_UNESCAPED_UNICODE);
            $promo2Desc = json_encode(['ar' => 'خصم ثابت على باقات معينة.', 'en' => 'Fixed discount on selected packages.'], JSON_UNESCAPED_UNICODE);

            DB::table('promotions')->updateOrInsert(
                ['name' => $promo2Name],
                [
                    'description' => $promo2Desc,
                    'starts_at'   => $today,
                    'ends_at'     => '2027-01-01',
                    'is_active'   => true,
                    'created_by'  => $actorId,
                    'updated_by'  => $actorId,
                    'created_at'  => $now,
                    'updated_at'  => $now,
                    'deleted_at'  => null,
                ]
            );

            $promo2Id = DB::table('promotions')->where('name', $promo2Name)->value('id');

            $applyAllPackages2 = empty($packages);

            DB::table('promotion_coupons')->updateOrInsert(
                ['code' => Str::upper('PACK50')],
                [
                    'promotion_id'        => $promo2Id,
                    'code'                => Str::upper('PACK50'),
                    'is_active'           => true,
                    'starts_at'           => $today,
                    'ends_at'             => null,

                    'applies_to'          => 'package',
                    'apply_all_services'  => false,
                    'apply_all_packages'  => $applyAllPackages2,

                    'discount_type'       => 'fixed',
                    'discount_value'      => 50,

                    'usage_limit_total'   => 50,
                    'usage_limit_per_user'=> 1,
                    'used_count'          => 0,

                    'min_invoice_total'   => 100,
                    'max_discount'        => null,

                    'meta'                => json_encode([
                        'note'     => 'Test coupon for packages',
                        'packages' => $applyAllPackages2 ? [] : $packages,
                    ], JSON_UNESCAPED_UNICODE),

                    'created_by'          => $actorId,
                    'updated_by'          => $actorId,
                    'created_at'          => $now,
                    'updated_at'          => $now,
                    'deleted_at'          => null,
                ]
            );

            // -----------------------------------------
            // 3) Promotion: BOTH 15% (coupon scope = both) with max_discount=60
            // -----------------------------------------
            $promo3Name = json_encode(['ar' => 'خصم 15% على خدمة + باقة', 'en' => '15% off service + package'], JSON_UNESCAPED_UNICODE);
            $promo3Desc = json_encode(['ar' => 'خصم عند شراء عناصر محددة.', 'en' => 'Discount when invoice contains specific items.'], JSON_UNESCAPED_UNICODE);

            DB::table('promotions')->updateOrInsert(
                ['name' => $promo3Name],
                [
                    'description' => $promo3Desc,
                    'starts_at'   => $today,
                    'ends_at'     => '2027-01-01',
                    'is_active'   => true,
                    'created_by'  => $actorId,
                    'updated_by'  => $actorId,
                    'created_at'  => $now,
                    'updated_at'  => $now,
                    'deleted_at'  => null,
                ]
            );

            $promo3Id = DB::table('promotions')->where('name', $promo3Name)->value('id');

            $oneService = !empty($services) ? [$services[0]] : [];
            $onePackage = !empty($packages) ? [$packages[0]] : [];

            DB::table('promotion_coupons')->updateOrInsert(
                ['code' => Str::upper('BOTH15')],
                [
                    'promotion_id'        => $promo3Id,
                    'code'                => Str::upper('BOTH15'),
                    'is_active'           => true,
                    'starts_at'           => $today,
                    'ends_at'             => null,

                    'applies_to'          => 'both',
                    'apply_all_services'  => empty($oneService), // إذا ما في بيانات، خلّيها apply all للتجربة
                    'apply_all_packages'  => empty($onePackage),

                    'discount_type'       => 'percent',
                    'discount_value'      => 15,

                    'usage_limit_total'   => null,
                    'usage_limit_per_user'=> null,
                    'used_count'          => 0,

                    'min_invoice_total'   => null,
                    'max_discount'        => 60,

                    'meta'                => json_encode([
                        'note'     => 'Test coupon for both',
                        'services' => $oneService,
                        'packages' => $onePackage,
                    ], JSON_UNESCAPED_UNICODE),

                    'created_by'          => $actorId,
                    'updated_by'          => $actorId,
                    'created_at'          => $now,
                    'updated_at'          => $now,
                    'deleted_at'          => null,
                ]
            );
        });
    }
}