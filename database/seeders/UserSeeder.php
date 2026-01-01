<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $password = Hash::make('123456');

        $users = [
            // Admin
            [
                'name' => 'Admin',
                'user_type' => 'admin',
                'mobile' => '0599000000',
                'email' => 'admin@dev.com',
                'password' => $password,
                'is_active' => true,
            ],

            // Bikers
            [
                'name' => 'Biker 01',
                'user_type' => 'biker',
                'mobile' => '0599000001',
                'email' => 'biker01@dev.com',
                'password' => $password,
                'is_active' => true,
            ],
            [
                'name' => 'Biker 02',
                'user_type' => 'biker',
                'mobile' => '0599000002',
                'email' => 'biker02@dev.com',
                'password' => $password,
                'is_active' => true,
            ],

            // Customers
            [
                'name' => 'Customer - Yaseen',
                'user_type' => 'customer',
                'mobile' => '0595587368',
                'email' => 'yaseen@dev.com',
                'password' => $password,
                'is_active' => true,
                'customer_group_id' => 2,
            ],
            [
                'name' => 'Customer - Yehya',
                'user_type' => 'customer',
                'mobile' => '0597725696',
                'email' => 'yehya@dev.com',
                'password' => $password,
                'is_active' => true,
                'customer_group_id' => 2,
            ],
        ];

        foreach ($users as $user) {
            $existing = DB::table('users')->where('email', $user['email'])->first();

            if ($existing) {
                // Update (بدون ما نلمس created_at)
                DB::table('users')
                    ->where('email', $user['email'])
                    ->update(array_merge($user, ['updated_at' => $now]));
            } else {
                // Insert
                DB::table('users')->insert(array_merge($user, [
                    'created_at' => $now,
                    'updated_at' => $now,
                ]));
            }
        }
    }
}