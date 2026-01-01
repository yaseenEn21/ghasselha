<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PointsSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $rows = [
            ['key' => 'points.redeem_points', 'value' => '100'],
            ['key' => 'points.redeem_amount', 'value' => '10'],
            ['key' => 'points.min_redeem_points', 'value' => '100'],
        ];

        foreach ($rows as $row) {
            DB::table('settings')->updateOrInsert(
                ['key' => $row['key']],
                [
                    'value' => $row['value'],
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }
    }
}
