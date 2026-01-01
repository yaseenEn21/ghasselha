<?php

namespace Database\Seeders;

use App\Models\Package;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PackageSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            $actorId = User::query()->where('user_type', 'admin')->value('id')
                ?? User::query()->value('id');

            $packages = [
                [
                    'name' => ['ar' => 'باقة 5 غسلات', 'en' => '5 Washes Package'],
                    'description' => ['ar' => 'باقة اقتصادية مناسبة للاستخدام الأسبوعي.', 'en' => 'An economical package suitable for weekly use.'],
                    'price' => 250.00,
                    'discounted_price' => 200.00,
                    'validity_days' => 30,
                    'washes_count' => 5,
                    'sort_order' => 1,
                    'is_active' => true,
                ],
                [
                    'name' => ['ar' => 'باقة 10 غسلات', 'en' => '10 Washes Package'],
                    'description' => ['ar' => 'باقة ممتازة مع عدد غسلات أعلى.', 'en' => 'A great package with a higher number of washes.'],
                    'price' => 450.00,
                    'discounted_price' => 399.00,
                    'validity_days' => 60,
                    'washes_count' => 10,
                    'sort_order' => 2,
                    'is_active' => true,
                ],
                [
                    'name' => ['ar' => 'باقة تجربة 2 غسلة', 'en' => '2 Washes Trial Package'],
                    'description' => ['ar' => 'جرّب الخدمة بسعر مخفض.', 'en' => 'Try the service at a discounted price.'],
                    'price' => 120.00,
                    'discounted_price' => 99.00,
                    'validity_days' => 14,
                    'washes_count' => 2,
                    'sort_order' => 3,
                    'is_active' => true,
                ],
            ];

            foreach ($packages as $p) {
                $package = Package::withTrashed()->updateOrCreate(
                    ['name' => $p['name']],
                    array_merge($p, [
                        'created_by' => $actorId,
                        'updated_by' => $actorId,
                    ])
                );

                if ($package->trashed()) {
                    $package->restore();
                }
            }
        });
    }
}
