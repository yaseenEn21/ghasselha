<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PointsTestSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        // جرّب على user_id=4 مثلاً
        $userId = 4;

        // point wallet
        DB::table('point_wallets')->updateOrInsert(
            ['user_id' => $userId],
            [
                'balance_points' => 1000,
                'total_earned_points' => 1000,
                'total_spent_points' => 0,
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );

        // transactions
        DB::table('point_transactions')->insert([
            [
                'user_id' => $userId,
                'type' => 'earn',
                'points' => 400,
                'money_amount' => null,
                'currency' => 'SAR',
                'note' => 'Earn from booking',
                'is_archived' => false,
                'created_at' => $now->copy()->subDays(2),
                'updated_at' => $now->copy()->subDays(2),
            ],
            [
                'user_id' => $userId,
                'type' => 'earn',
                'points' => 600,
                'money_amount' => null,
                'currency' => 'SAR',
                'note' => 'Earn from promotion',
                'is_archived' => false,
                'created_at' => $now->copy()->subDays(1),
                'updated_at' => $now->copy()->subDays(1),
            ],
        ]);
    }
}
