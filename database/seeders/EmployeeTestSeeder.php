<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\EmployeeWeeklyInterval;
use App\Models\EmployeeTimeBlock;
use App\Models\EmployeeWorkArea;
use App\Models\User;
use App\Models\Service;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EmployeeTestSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            $now = now();

            $actorId = DB::table('users')->where('user_type', 'admin')->value('id')
                ?? DB::table('users')->orderBy('id')->value('id');

            // 1) Ensure bikers exist
            $biker1 = User::firstOrCreate(
                ['mobile' => '0500000001'],
                [
                    'name' => 'Mohammad Biker',
                    'user_type' => 'biker',
                    'email' => null,
                    'password' => Hash::make('123456'),
                    'is_active' => true,
                    'notification' => true,
                    'created_by' => $actorId,
                    'updated_by' => $actorId,
                ]
            );

            $biker2 = User::firstOrCreate(
                ['mobile' => '0500000002'],
                [
                    'name' => 'Khaled Biker',
                    'user_type' => 'biker',
                    'email' => null,
                    'password' => Hash::make('123456'),
                    'is_active' => true,
                    'notification' => true,
                    'created_by' => $actorId,
                    'updated_by' => $actorId,
                ]
            );

            // 2) Employees
            $emp1 = Employee::firstOrCreate(
                ['user_id' => $biker1->id],
                ['is_active' => true, 'created_by' => $actorId, 'updated_by' => $actorId]
            );

            $emp2 = Employee::firstOrCreate(
                ['user_id' => $biker2->id],
                ['is_active' => true, 'created_by' => $actorId, 'updated_by' => $actorId]
            );

            // 3) Services assignment (first 3 services)
            $serviceIds = Service::query()->where('is_active', true)->orderBy('id')->limit(3)->pluck('id')->all();
            if (count($serviceIds)) {
                $emp1->services()->syncWithPivotValues($serviceIds, ['is_active' => true]);
                $emp2->services()->syncWithPivotValues($serviceIds, ['is_active' => true]);
            }

            // 4) Weekly intervals template:
            // weekdays: 0..6 (Sunday..Saturday). مثال: السبت=6
            // Work 08:00-13:30, Break 13:30-16:00, Work 16:00-23:59
            $template = [
                ['type' => 'work', 'start' => '08:00', 'end' => '13:30'],
                ['type' => 'break', 'start' => '13:30', 'end' => '16:00'],
                ['type' => 'work', 'start' => '16:00', 'end' => '23:59'],
            ];

            // Clear old
            EmployeeWeeklyInterval::whereIn('employee_id', [$emp1->id, $emp2->id])->delete();

            foreach ([$emp1, $emp2] as $emp) {

                $days = ['saturday','sunday','monday','tuesday','wednesday','thursday','friday'];

                foreach ($days as $day) {
                    foreach ($template as $t) {
                        EmployeeWeeklyInterval::create([
                            'employee_id' => $emp->id,
                            'day' => $day,
                            'type' => $t['type'],
                            'start_time' => $t['start'],
                            'end_time' => $t['end'],
                            'is_active' => true,
                            'created_by' => $actorId,
                            'updated_by' => $actorId,
                        ]);
                    }
                }
            }

            // 5) Block time (today) for emp1: 15:00-18:00
            EmployeeTimeBlock::where('employee_id', $emp1->id)->delete();

            EmployeeTimeBlock::create([
                'employee_id' => $emp1->id,
                'date' => $now->toDateString(),
                'start_time' => '15:00',
                'end_time' => '18:00',
                'reason' => 'Emergency',
                'is_active' => true,
                'created_by' => $actorId,
                'updated_by' => $actorId,
            ]);

            // 6) Work area polygon (Jeddah example) — واحد لكل موظف
            // Polygon لازم يكون داخل السعودية حسب طلبك
            $polygonJeddah = [
                ['lat' => 21.6500000, 'lng' => 39.1000000],
                ['lat' => 21.6500000, 'lng' => 39.3500000],
                ['lat' => 21.4500000, 'lng' => 39.3500000],
                ['lat' => 21.4500000, 'lng' => 39.1000000],
            ];

            foreach ([$emp1, $emp2] as $emp) {
                $lats = array_map(fn($p) => (float) $p['lat'], $polygonJeddah);
                $lngs = array_map(fn($p) => (float) $p['lng'], $polygonJeddah);

                EmployeeWorkArea::updateOrCreate(
                    ['employee_id' => $emp->id],
                    [
                        'polygon' => $polygonJeddah,
                        'min_lat' => min($lats),
                        'max_lat' => max($lats),
                        'min_lng' => min($lngs),
                        'max_lng' => max($lngs),
                        'is_active' => true,
                        'created_by' => $actorId,
                        'updated_by' => $actorId,
                    ]
                );
            }
        });
    }
}