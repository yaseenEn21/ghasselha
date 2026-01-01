<?php

namespace Database\Seeders;

use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceCategorySeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            $userId = User::query()
                ->where('user_type', 'admin')
                ->value('id')
                ?? User::query()->value('id');

            $categories = [
                [
                    'name' => ['ar' => 'غسيل خارجي', 'en' => 'Exterior Wash'],
                    'sort_order' => 1,
                    'is_active' => true
                ],
                [
                    'name' => ['ar' => 'غسيل داخلي', 'en' => 'Interior Cleaning'],
                    'sort_order' => 2,
                    'is_active' => true
                ],
                [
                    'name' => ['ar' => 'تلميع وحماية', 'en' => 'Polish & Protection'],
                    'sort_order' => 3,
                    'is_active' => true
                ],
                [
                    'name' => ['ar' => 'إضافات', 'en' => 'Add-ons'],
                    'sort_order' => 4,
                    'is_active' => true
                ],
            ];

            foreach ($categories as $item) {
                $category = ServiceCategory::withTrashed()->updateOrCreate(
                    ['name' => $item['name']],
                    [
                        'sort_order' => $item['sort_order'],
                        'is_active' => $item['is_active'],
                        'created_by' => $userId,
                        'updated_by' => $userId,
                    ]
                );

                // لو كان محذوف soft delete، رجّعه
                if ($category->trashed()) {
                    $category->restore();
                }
            }
        });
    }
}
