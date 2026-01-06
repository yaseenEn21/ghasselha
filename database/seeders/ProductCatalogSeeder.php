<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        DB::transaction(function () use ($now) {

            $actorId = DB::table('users')->where('user_type', 'admin')->value('id')
                ?? DB::table('users')->orderBy('id')->value('id');

            $categories = [
                ['name' => ['ar' => 'منتجات إضافية', 'en' => 'Add-on Products'], 'sort_order' => 1, 'is_active' => true],
            ];

            foreach ($categories as $c) {
                ProductCategory::updateOrCreate(
                    ['name->ar' => $c['name']['ar']],
                    [
                        'name' => $c['name'],
                        'sort_order' => $c['sort_order'],
                        'is_active' => $c['is_active'],
                        'created_by' => $actorId,
                        'updated_by' => $actorId,
                    ]
                );
            }

            $catId = ProductCategory::where('name->ar', 'منتجات إضافية')->value('id');

            $products = [
                [
                    'product_category_id' => $catId,
                    'name' => ['ar' => 'مجموعة غسيل سيارات 15 قطعة', 'en' => 'Car Wash Kit (15 pcs)'],
                    'description' => ['ar' => 'مجموعة أدوات غسيل مناسبة للاستخدام المنزلي.', 'en' => 'A complete car wash tools kit for home use.'],
                    'cost' => 10.00,
                    'price' => 16.75,
                    'discounted_price' => null,
                    'max_qty_per_booking' => 10,
                    'sort_order' => 1,
                    'is_active' => true,
                ],
                [
                    'product_category_id' => $catId,
                    'name' => ['ar' => 'إسفنجة تنظيف', 'en' => 'Cleaning Sponge'],
                    'description' => ['ar' => 'إسفنجة ناعمة لتنظيف السيارة.', 'en' => 'Soft sponge for car cleaning.'],
                    'cost' => 5.00,
                    'price' => 8.50,
                    'discounted_price' => 7.00,
                    'max_qty_per_booking' => 10,
                    'sort_order' => 2,
                    'is_active' => true,
                ],
                [
                    'product_category_id' => $catId,
                    'name' => ['ar' => 'منشفة مايكروفايبر', 'en' => 'Microfiber Towel'],
                    'description' => ['ar' => 'منشفة لتجفيف السيارة بدون خدوش.', 'en' => 'Scratch-free microfiber drying towel.'],
                    'cost' => 6.00,
                    'price' => 12.00,
                    'discounted_price' => null,
                    'max_qty_per_booking' => 10,
                    'sort_order' => 3,
                    'is_active' => true,
                ],
            ];

            foreach ($products as $p) {
                Product::updateOrCreate(
                    ['name->ar' => $p['name']['ar']],
                    array_merge($p, [
                        'created_by' => $actorId,
                        'updated_by' => $actorId,
                    ])
                );
            }
        });
    }
}