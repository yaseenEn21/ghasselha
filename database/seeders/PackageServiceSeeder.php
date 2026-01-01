<?php

namespace Database\Seeders;

use App\Models\Package;
use App\Models\Service;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PackageServiceSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            $services = Service::query()
                ->where('is_active', true)
                ->orderBy('id')
                ->limit(6)
                ->get(['id']);

            if ($services->count() < 1) {
                return;
            }

            $pkg5  = Package::query()->where('name', 'باقة 5 غسلات')->first();
            $pkg10 = Package::query()->where('name', 'باقة 10 غسلات')->first();
            $pkg2  = Package::query()->where('name', 'باقة تجربة 2 غسلة')->first();

            // Helper لبناء sync array مع pivot
            $buildSync = function ($ids) {
                $sync = [];
                $i = 1;
                foreach ($ids as $serviceId) {
                    $sync[$serviceId] = [
                        'sort_order' => $i++,
                    ];
                }
                return $sync;
            };

            // باقة 2: تربط خدمة واحدة أو اثنتين
            if ($pkg2) {
                $ids = $services->take(1)->pluck('id')->all();
                $pkg2->services()->sync($buildSync($ids));
            }

            // باقة 5: تربط 2-3 خدمات
            if ($pkg5) {
                $ids = $services->take(3)->pluck('id')->all();
                $pkg5->services()->sync($buildSync($ids));
            }

            // باقة 10: تربط 4-6 خدمات
            if ($pkg10) {
                $ids = $services->take(6)->pluck('id')->all();
                $pkg10->services()->sync($buildSync($ids));
            }
        });
    }
}
