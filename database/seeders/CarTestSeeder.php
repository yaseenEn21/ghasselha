<?php

namespace Database\Seeders;

use App\Models\Car;
use App\Models\VehicleMake;
use App\Models\VehicleModel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CarTestSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $make = VehicleMake::orderBy('id')->first();
            if (!$make) return;

            $model = VehicleModel::where('vehicle_make_id', $make->id)->orderBy('id')->first();
            if (!$model) return;

            Car::updateOrCreate(
                ['user_id' => 4, 'plate_number' => '1234', 'plate_letters' => 'ABC'],
                [
                    'vehicle_make_id' => $make->id,
                    'vehicle_model_id' => $model->id,
                    'color' => 'black',
                    'is_default' => true,
                ]
            );

            Car::updateOrCreate(
                ['user_id' => 4, 'plate_number' => '5678', 'plate_letters' => 'XYZ'],
                [
                    'vehicle_make_id' => $make->id,
                    'vehicle_model_id' => $model->id,
                    'color' => 'white',
                    'is_default' => true,
                ]
            );
        });
    }
}