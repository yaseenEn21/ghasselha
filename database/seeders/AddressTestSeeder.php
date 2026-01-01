<?php

namespace Database\Seeders;

use App\Models\Address;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AddressTestSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            Address::where('user_id', 1)->delete();

            Address::create([
                'user_id' => 4,
                'type' => 'home',
                'country' => 'Saudi Arabia',
                'city' => 'Jeddah',
                'area' => 'Al Hamdaniyah',
                'address_line' => 'Al Hamdaniyah District - Jeddah',
                'building_name' => 'لا يوجد',
                'building_number' => '15',
                'landmark' => 'تموينات العزيزية',
                'lat' => 21.5433333,
                'lng' => 39.2405556,
                'is_default' => true,
            ]);

            Address::create([
                'user_id' => 4,
                'type' => 'work',
                'country' => 'Saudi Arabia',
                'city' => 'Jeddah',
                'area' => 'Al Hamdaniyah',
                'address_line' => 'Work Location - Jeddah',
                'building_name' => 'برج رقم واحد',
                'building_number' => '5',
                'landmark' => 'برج الوحدة',
                'lat' => 21.5561111,
                'lng' => 39.2258333,
                'is_default' => false,
            ]);
        });
    }
}