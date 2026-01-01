<?php

namespace Database\Seeders;

use App\Models\VehicleMake;
use App\Models\VehicleModel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class VehicleCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/data/Vehicles List 2025.xlsx');

        if (!file_exists($path)) {
            $this->command?->warn("Excel file not found: {$path}");
            return;
        }

        DB::transaction(function () use ($path) {

            $sheet = IOFactory::load($path)->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, true);

            // أول صف headers
            $header = array_shift($rows);

            // mapping headers by text
            $col = function ($name) use ($header) {
                foreach ($header as $k => $v) {
                    if (trim((string)$v) === $name) return $k;
                }
                return null;
            };

            $cMakeId   = $col('ID');
            $cMakeAr   = $col('Name');
            $cMakeEn   = $col('NameEn');
            $cModelId  = $col('CarBrandModels → ID');
            $cModelAr  = $col('CarBrandModels → Name');
            $cModelEn  = $col('CarBrandModels → NameEn');

            if (!$cMakeId || !$cModelId) {
                throw new \RuntimeException('Excel columns not matched.');
            }

            // 1) makes
            $makeMap = []; // external_id => make_id

            foreach ($rows as $r) {
                $makeExternal = (int)($r[$cMakeId] ?? 0);
                if ($makeExternal <= 0) continue;

                if (!isset($makeMap[$makeExternal])) {
                    $make = VehicleMake::updateOrCreate(
                        ['external_id' => $makeExternal],
                        [
                            'name' => [
                                'ar' => (string)($r[$cMakeAr] ?? ''),
                                'en' => (string)($r[$cMakeEn] ?? ''),
                            ],
                            'is_active' => true,
                            'sort_order' => 0,
                        ]
                    );

                    $makeMap[$makeExternal] = $make->id;
                }
            }

            // 2) models
            foreach ($rows as $r) {
                $makeExternal = (int)($r[$cMakeId] ?? 0);
                $modelExternal = (int)($r[$cModelId] ?? 0);

                if ($makeExternal <= 0 || $modelExternal <= 0) continue;

                $makeId = $makeMap[$makeExternal] ?? null;
                if (!$makeId) continue;

                VehicleModel::updateOrCreate(
                    [
                        'vehicle_make_id' => $makeId,
                        'external_id' => $modelExternal,
                    ],
                    [
                        'name' => [
                            'ar' => (string)($r[$cModelAr] ?? ''),
                            'en' => (string)($r[$cModelEn] ?? ''),
                        ],
                        'is_active' => true,
                        'sort_order' => 0,
                    ]
                );
            }
        });
    }
}