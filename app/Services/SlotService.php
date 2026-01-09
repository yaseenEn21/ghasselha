<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Employee;
use App\Models\Service;
use Carbon\Carbon;

class SlotService
{
    /**
     * @return array{
     *   items: array<int, array{start_time:string,end_time:string,employees:array<int,array{employee_id:int,user_id:int,name:string}>}>,
     *   meta: array<string,mixed>
     * }
     */
    public function getSlots(string $date, int $serviceId, float $lat, float $lng, ?int $stepMinutes = null, string $mode = 'blocks'): array
    {
        $tz = config('app.timezone', 'UTC');
        $day = Carbon::createFromFormat('d-m-Y', $date, $tz);
        $dbDate = $day->toDateString();

        $service = Service::query()
            ->where('id', $serviceId)
            ->where('is_active', true)
            ->whereHas('category', fn($q) => $q->where('is_active', true))
            ->first();

        if (!$service) {
            return [
                'items' => [],
                'meta' => [
                    'date' => $date,
                    'service_id' => $serviceId,
                    'error_code' => 'SERVICE_NOT_FOUND',
                    'error' => 'Service not found',
                ],
            ];
        }

        $duration = (int) $service->duration_minutes;
        $step = $stepMinutes ?? (int) config('booking.slot_step_minutes', 60);
        $weekday = $this->carbonToDayEnum($day);

        // ✅ base query (بدون bbox/polygon)
        $baseQuery = Employee::query()
            ->where('is_active', true)
            ->whereHas('user', fn($q) => $q->where('is_active', true)->where('user_type', 'biker'))
            ->whereHas('services', function ($q) use ($serviceId) {
                $q->where('services.id', $serviceId)
                    ->where('employee_services.is_active', 1);
            });

        $employeesForServiceCount = (clone $baseQuery)->count();

        // ✅ bbox filter
        $employees = (clone $baseQuery)
            ->whereHas('workArea', function ($q) use ($lat, $lng) {
                $q->where('is_active', true)
                    ->where('min_lat', '<=', $lat)
                    ->where('max_lat', '>=', $lat)
                    ->where('min_lng', '<=', $lng)
                    ->where('max_lng', '>=', $lng);
            })
            ->with([
                'user:id,name',
                'workArea:id,employee_id,polygon,min_lat,max_lat,min_lng,max_lng',
                'weeklyIntervals' => function ($q) use ($weekday) {
                    $q->where('day', $weekday)->where('is_active', true);
                },
                'timeBlocks' => function ($q) use ($dbDate) {
                    $q->where('date', $dbDate)->where('is_active', true);
                },
            ])
            ->get();

        if ($employees->isEmpty()) {
            return [
                'items' => [],
                'meta' => [
                    'date' => $date,
                    'day' => $weekday,
                    'service_id' => $serviceId,
                    'lat' => (string) $lat,
                    'lng' => (string) $lng,
                    'employees_for_service' => $employeesForServiceCount,
                    'employees_in_bbox' => 0,
                    'employees_in_polygon' => 0,
                    'error_code' => 'OUT_OF_COVERAGE',
                    'error' => 'Address is outside service coverage area (bbox)',
                ],
            ];
        }

        // ✅ polygon filter
        $candidates = $employees->filter(function ($emp) use ($lat, $lng) {
            $poly = $emp->workArea?->polygon ?? [];
            return $this->pointInPolygon($lat, $lng, $poly);
        })->values();

        if ($candidates->isEmpty()) {
            return [
                'items' => [],
                'meta' => [
                    'date' => $date,
                    'day' => $weekday,
                    'service_id' => $serviceId,
                    'lat' => (string) $lat,
                    'lng' => (string) $lng,
                    'employees_for_service' => $employeesForServiceCount,
                    'employees_in_bbox' => $employees->count(),
                    'employees_in_polygon' => 0,
                    'error_code' => 'OUT_OF_COVERAGE',
                    'error' => 'Address is outside service coverage area (polygon)',
                ],
            ];
        }

        $grouped = [];
        $noWorkCount = 0;
        $totalGeneratedSlots = 0;

        foreach ($candidates as $emp) {
            $work = $emp->weeklyIntervals->where('type', 'work')->values();
            $breaks = $emp->weeklyIntervals->where('type', 'break')->values();

            if ($work->isEmpty()) {
                $noWorkCount++;
                continue;
            }

            $workIntervals = $work->map(fn($i) => [$this->timeToMinutes($i->start_time), $this->timeToMinutes($i->end_time)])->all();
            $breakIntervals = $breaks->map(fn($i) => [$this->timeToMinutes($i->start_time), $this->timeToMinutes($i->end_time)])->all();

            $blockIntervals = $emp->timeBlocks->map(fn($b) => [$this->timeToMinutes($b->start_time), $this->timeToMinutes($b->end_time)])->all();

            $available = $this->subtractIntervals($workIntervals, $breakIntervals);
            $available = $this->subtractIntervals($available, $blockIntervals);

            $bookingIntervals = Booking::query()
                ->where('employee_id', $emp->id)
                ->where('booking_date', $dbDate)
                ->whereNotIn('status', ['cancelled'])
                ->get(['start_time', 'end_time'])
                ->map(fn($b) => [$this->timeToMinutes($b->start_time), $this->timeToMinutes($b->end_time)])
                ->all();

            $available = $this->subtractIntervals($available, $bookingIntervals);

            $slots = $this->generateSlots($available, $duration, $step, $mode);

            foreach ($slots as $s) {
                $totalGeneratedSlots++;
                $key = $s['start_time'] . '|' . $s['end_time'];
                if (!isset($grouped[$key])) {
                    $grouped[$key] = [
                        'start_time' => $s['start_time'],
                        'end_time' => $s['end_time'],
                        'employees' => [],
                    ];
                }

                $grouped[$key]['employees'][] = [
                    'employee_id' => (int) $emp->id,
                    'user_id' => (int) $emp->user_id,
                    'name' => (string) ($emp->user?->name ?? ''),
                ];
            }
        }

        $items = array_values($grouped);
        usort($items, fn($a, $b) => strcmp($a['start_time'], $b['start_time']));

        // ✅ لو ما طلع ولا slot: حدّد السبب
        $meta = [
            'date' => $date,
            'day' => $weekday,
            'service_id' => $serviceId,
            'duration_minutes' => $duration,
            'step_minutes' => $step,
            'lat' => (string) $lat,
            'lng' => (string) $lng,
            'employees_considered' => $candidates->count(),
        ];

        if (empty($items)) {
            if ($noWorkCount === $candidates->count()) {
                $meta['error_code'] = 'NO_WORKING_HOURS';
                $meta['error'] = 'No employees have working hours on this day';
            } else {
                $meta['error_code'] = 'NO_SLOTS_AVAILABLE';
                $meta['error'] = 'No slots available after breaks/blocks/bookings';
            }
        }

        return [
            'items' => $items,
            'meta' => $meta,
        ];
    }

    private function carbonToDayEnum(Carbon $day): string
    {
        // Carbon: 0=Sunday .. 6=Saturday
        return match ($day->dayOfWeek) {
            0 => 'sunday',
            1 => 'monday',
            2 => 'tuesday',
            3 => 'wednesday',
            4 => 'thursday',
            5 => 'friday',
            6 => 'saturday',
        };
    }

    private function timeToMinutes(string $time): int
    {
        // "HH:MM:SS" أو "HH:MM"
        [$h, $m] = array_map('intval', explode(':', substr($time, 0, 5)));
        return $h * 60 + $m;
    }

    private function minutesToTime(int $minutes): string
    {
        $minutes = max(0, min(24 * 60, $minutes));
        $h = intdiv($minutes, 60);
        $m = $minutes % 60;
        return str_pad((string) $h, 2, '0', STR_PAD_LEFT) . ':' . str_pad((string) $m, 2, '0', STR_PAD_LEFT);
    }

    /**
     * subtract many intervals (blocked) from base intervals
     * intervals are [startMin, endMin] with start < end
     */
    private function subtractIntervals(array $base, array $subtract): array
    {
        $base = $this->normalizeIntervals($base);
        $subtract = $this->normalizeIntervals($subtract);

        $result = $base;

        foreach ($subtract as [$bs, $be]) {
            $new = [];
            foreach ($result as [$s, $e]) {
                // no overlap
                if ($be <= $s || $bs >= $e) {
                    $new[] = [$s, $e];
                    continue;
                }
                // cut left
                if ($bs > $s) {
                    $new[] = [$s, $bs];
                }
                // cut right
                if ($be < $e) {
                    $new[] = [$be, $e];
                }
            }
            $result = $this->normalizeIntervals($new);
        }

        return $result;
    }

    private function normalizeIntervals(array $intervals): array
    {
        $clean = [];
        foreach ($intervals as $it) {
            if (!is_array($it) || count($it) < 2)
                continue;
            $s = (int) $it[0];
            $e = (int) $it[1];
            if ($e <= $s)
                continue;
            $clean[] = [$s, $e];
        }

        usort($clean, fn($a, $b) => $a[0] <=> $b[0]);

        // merge overlaps
        $merged = [];
        foreach ($clean as [$s, $e]) {
            if (empty($merged)) {
                $merged[] = [$s, $e];
                continue;
            }
            $lastIndex = count($merged) - 1;
            [$ls, $le] = $merged[$lastIndex];

            if ($s <= $le) {
                $merged[$lastIndex] = [$ls, max($le, $e)];
            } else {
                $merged[] = [$s, $e];
            }
        }

        return $merged;
    }

    private function generateSlots(array $available, int $durationMinutes, int $stepMinutes, string $mode = 'rolling'): array
    {
        $slots = [];

        foreach ($available as [$s, $e]) {

            if ($mode === 'blocks') {
                // ✅ يبدأ من بداية الدوام مباشرة، ويقفز كل مدة خدمة
                $t = $s;
                while ($t + $durationMinutes <= $e) {
                    $slots[] = [
                        'start_time' => $this->minutesToTime($t),
                        'end_time' => $this->minutesToTime($t + $durationMinutes),
                    ];
                    $t += $durationMinutes; // ✅ قفز 90 دقيقة
                }
                continue;
            }

            // rolling (الحالي)
            $t = $this->ceilToStep($s, $stepMinutes);
            while ($t + $durationMinutes <= $e) {
                $slots[] = [
                    'start_time' => $this->minutesToTime($t),
                    'end_time' => $this->minutesToTime($t + $durationMinutes),
                ];
                $t += $stepMinutes; // ✅ قفز 15 دقيقة
            }
        }

        return $slots;
    }

    private function ceilToStep(int $minutes, int $step): int
    {
        if ($step <= 1)
            return $minutes;
        $r = $minutes % $step;
        return $r === 0 ? $minutes : ($minutes + ($step - $r));
    }

    /**
     * Ray-casting point in polygon
     * polygon: array of ['lat'=>..,'lng'=>..]
     */
    private function pointInPolygon(float $lat, float $lng, array $polygon): bool
    {
        if (count($polygon) < 3)
            return false;

        $inside = false;
        $n = count($polygon);

        for ($i = 0, $j = $n - 1; $i < $n; $j = $i++) {
            $xi = (float) ($polygon[$i]['lat'] ?? 0);
            $yi = (float) ($polygon[$i]['lng'] ?? 0);
            $xj = (float) ($polygon[$j]['lat'] ?? 0);
            $yj = (float) ($polygon[$j]['lng'] ?? 0);

            $intersect = (($yi > $lng) !== ($yj > $lng))
                && ($lat < ($xj - $xi) * ($lng - $yi) / (($yj - $yi) ?: 1e-12) + $xi);

            if ($intersect)
                $inside = !$inside;
        }

        return $inside;
    }
}