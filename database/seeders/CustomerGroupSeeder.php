<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CustomerGroupSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        DB::transaction(function () use ($now) {

            $actorId = DB::table('users')->where('user_type', 'admin')->value('id')
                ?? DB::table('users')->orderBy('id')->value('id');

            // 1) Insert/Update Groups
            DB::table('customer_groups')->updateOrInsert(
                ['name' => 'Regular'],
                [
                    'is_active' => true,
                    'created_by' => $actorId,
                    'updated_by' => $actorId,
                    'deleted_at' => null,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );

            DB::table('customer_groups')->updateOrInsert(
                ['name' => 'VIP'],
                [
                    'is_active' => true,
                    'created_by' => $actorId,
                    'updated_by' => $actorId,
                    'deleted_at' => null,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );

            $regularId = DB::table('customer_groups')->where('name', 'Regular')->value('id');
            $vipId     = DB::table('customer_groups')->where('name', 'VIP')->value('id');

            // 2) Assign users (id 1,2) if exist
            if (DB::table('users')->where('id', 1)->exists()) {
                DB::table('users')->where('id', 1)->update([
                    'customer_group_id' => $vipId,
                    'updated_at' => $now,
                ]);
            }

            if (DB::table('users')->where('id', 2)->exists()) {
                DB::table('users')->where('id', 2)->update([
                    'customer_group_id' => $regularId,
                    'updated_at' => $now,
                ]);
            }
        });
    }
}