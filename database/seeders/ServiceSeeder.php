<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            $userId = User::query()
                ->where('user_type', 'admin')
                ->value('id')
                ?? User::query()->value('id');

            // نجيب التصنيفات بالاسم
            $cat = ServiceCategory::query()
                ->get(['id', 'name'])
                ->mapWithKeys(fn($c) => [($c->name['ar'] ?? '') => $c->id]);


            $services = [
                [
                    'category' => ['ar' => 'غسيل خارجي', 'en' => 'Exterior Wash'],
                    'name' => ['ar' => 'غسيل خارجي عادي', 'en' => 'Standard Exterior Wash'],
                    'description' => ['ar' => 'غسيل خارجي + تجفيف', 'en' => 'Exterior wash + drying'],
                    'duration_minutes' => 25,
                    'price' => 20.00,
                    'discounted_price' => 18.00,
                    'is_active' => true,
                ],
                [
                    'category' => ['ar' => 'غسيل خارجي', 'en' => 'Exterior Wash'],
                    'name' => ['ar' => 'غسيل خارجي + واكس', 'en' => 'Exterior Wash + Wax'],
                    'description' => ['ar' => 'غسيل خارجي + طبقة واكس خفيفة', 'en' => 'Exterior wash + light wax coating'],
                    'duration_minutes' => 40,
                    'price' => 35.00,
                    'discounted_price' => 30.00,
                    'is_active' => true,
                ],

                [
                    'category' => ['ar' => 'غسيل داخلي', 'en' => 'Interior Cleaning'],
                    'name' => ['ar' => 'تنظيف داخلي سريع', 'en' => 'Quick Interior Cleaning'],
                    'description' => ['ar' => 'كنس + مسح داخلي', 'en' => 'Vacuum + interior wipe'],
                    'duration_minutes' => 30,
                    'price' => 25.00,
                    'discounted_price' => null,
                    'is_active' => true,
                ],
                [
                    'category' => ['ar' => 'غسيل داخلي', 'en' => 'Interior Cleaning'],
                    'name' => ['ar' => 'تنظيف داخلي شامل', 'en' => 'Full Interior Detailing'],
                    'description' => ['ar' => 'كنس + تفصيل + تعقيم', 'en' => 'Vacuum + detailing + sanitizing'],
                    'duration_minutes' => 60,
                    'price' => 45.00,
                    'discounted_price' => 40.00,
                    'is_active' => true,
                ],

                [
                    'category' => ['ar' => 'تلميع وحماية', 'en' => 'Polish & Protection'],
                    'name' => ['ar' => 'تلميع سريع', 'en' => 'Quick Polish'],
                    'description' => ['ar' => 'تلميع خارجي سريع', 'en' => 'Quick exterior polish'],
                    'duration_minutes' => 60,
                    'price' => 80.00,
                    'discounted_price' => null,
                    'is_active' => true,
                ],
                [
                    'category' => ['ar' => 'تلميع وحماية', 'en' => 'Polish & Protection'],
                    'name' => ['ar' => 'حماية نانو', 'en' => 'Nano Protection'],
                    'description' => ['ar' => 'طبقة حماية نانو (حسب توفر الخدمة)', 'en' => 'Nano protective coating (subject to availability)'],
                    'duration_minutes' => 120,
                    'price' => 180.00,
                    'discounted_price' => 160.00,
                    'is_active' => true,
                ],

                [
                    'category' => ['ar' => 'إضافات', 'en' => 'Add-ons'],
                    'name' => ['ar' => 'تعطير السيارة', 'en' => 'Car Fragrance'],
                    'description' => null,
                    'duration_minutes' => 10,
                    'price' => 5.00,
                    'discounted_price' => null,
                    'is_active' => true,
                ],
                [
                    'category' => ['ar' => 'إضافات', 'en' => 'Add-ons'],
                    'name' => ['ar' => 'تلميع إطارات', 'en' => 'Tire Shine'],
                    'description' => null,
                    'duration_minutes' => 10,
                    'price' => 7.00,
                    'discounted_price' => null,
                    'is_active' => true,
                ],
            ];

            foreach ($services as $item) {
                $categoryId = $cat[$item['category']['ar']] ?? null;
                if (!$categoryId) {
                    // إذا التصنيف غير موجود لأي سبب، تخطّي
                    continue;
                }

                $service = Service::withTrashed()->updateOrCreate(
                    [
                        'service_category_id' => $categoryId,
                        'name' => $item['name'],
                    ],
                    [
                        'description' => $item['description'],
                        'duration_minutes' => $item['duration_minutes'],
                        'price' => $item['price'],
                        'discounted_price' => $item['discounted_price'],
                        'is_active' => $item['is_active'],
                        'created_by' => $userId,
                        'updated_by' => $userId,
                    ]
                );

                if ($service->trashed()) {
                    $service->restore();
                }
            }
        });
    }
}