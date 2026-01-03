<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Models\EmployeeWeeklyInterval;
use App\Models\EmployeeWorkArea;

class EmployeeController extends Controller
{
    protected string $title;
    protected string $page_title;

    public function __construct()
    {
        $this->middleware('can:employees.view')->only(['index', 'show']);
        $this->middleware('can:employees.create')->only(['create', 'store']);
        $this->middleware('can:employees.edit')->only(['edit', 'update']);
        $this->middleware('can:employees.delete')->only(['destroy']);

        $this->title = t('employees.list');
        $this->page_title = t('employees.title');

        view()->share([
            'title' => $this->title,
            'page_title' => $this->page_title,
        ]);
    }

    public function index(DataTables $datatable, Request $request)
    {
        if ($request->ajax()) {
            $query = Employee::query()
                ->with([
                    'user' => function ($q) {
                        $q->select('id', 'name', 'mobile', 'email', 'is_active', 'gender', 'user_type');
                    }
                ])
                ->select('employees.*');

            // فقط المستخدمين من نوع biker (حسب طلبك)
            $query->whereHas('user', function ($q) {
                $q->where('user_type', 'biker');
            });

            // بحث
            if ($search = $request->get('search_custom')) {
                $search = trim($search);
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('mobile', 'like', "%{$search}%");
                });
            }

            // فلتر الحالة
            if ($status = $request->get('status')) {
                if ($status === 'active') {
                    $query->where('is_active', true);
                } elseif ($status === 'inactive') {
                    $query->where('is_active', false);
                }
            }

            // فلتر الجنس
            if ($gender = $request->get('gender')) {
                if (in_array($gender, ['male', 'female'])) {
                    $query->whereHas('user', function ($q) use ($gender) {
                        $q->where('gender', $gender);
                    });
                }
            }

            return $datatable->eloquent($query)
                ->addColumn('name', fn(Employee $row) => e($row->user?->name ?? '—'))
                ->addColumn('mobile', fn(Employee $row) => e($row->user?->mobile ?? '—'))
                ->addColumn('email', fn(Employee $row) => e($row->user?->email ?? '—'))
                ->addColumn('gender', function (Employee $row) {
                    return $row->user?->gender === 'female'
                        ? __('employees.fields.gender_female')
                        : __('employees.fields.gender_male');
                })
                ->addColumn('status_badge', function (Employee $row) {
                    $isActive = $row->is_active && $row->user?->is_active;

                    return $isActive
                        ? '<span class="badge badge-light-success">' . e(__('employees.status_active')) . '</span>'
                        : '<span class="badge badge-light-danger">' . e(__('employees.status_inactive')) . '</span>';
                })
                ->editColumn('created_at', fn(Employee $row) => optional($row->created_at)->format('Y-m-d'))
                ->addColumn('actions', function (Employee $row) {
                    return view('dashboard.employees._actions', [
                        'employee' => $row,
                    ])->render();
                })
                ->rawColumns(['status_badge', 'actions'])
                ->make(true);
        }

        return view('dashboard.employees.index');
    }

    public function create()
    {
        $services = Service::select('id', 'name')
            ->orderBy('sort_order')
            ->get();

        return view('dashboard.employees.create', compact('services'));
    }

    public function store(Request $request)
    {
        // الأيام المسموحة في الـ schema
        $days = [
            'saturday',
            'sunday',
            'monday',
            'tuesday',
            'wednesday',
            'thursday',
            'friday',
        ];

        // قواعد الفاليديشين الأساسية
        $rules = [
            'name' => ['required', 'string', 'max:190'],
            'mobile' => ['required', 'string', 'max:20', 'unique:users,mobile'],
            'email' => ['nullable', 'email', 'max:190', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],

            'birth_date' => ['nullable', 'date'],
            'gender' => ['required', 'in:male,female'],

            'is_active' => ['nullable', 'boolean'],
            'notification' => ['nullable', 'boolean'],

            // الخدمات
            'services' => ['nullable', 'array'],
            'services.*' => ['integer', 'exists:services,id'],

            // ساعات العمل / الاستراحة
            'work' => ['nullable', 'array'],
            'break' => ['nullable', 'array'],

            // الخريطة (JSON string)
            'work_area_polygon' => ['nullable', 'string'],
        ];

        // قواعد الوقت لكل يوم (work/break)
        foreach ($days as $day) {
            $rules["work.$day.start_time"] = ['nullable', 'date_format:H:i'];
            $rules["work.$day.end_time"] = ['nullable', 'date_format:H:i'];
            $rules["work.$day.is_active"] = ['nullable', 'boolean'];

            $rules["break.$day.start_time"] = ['nullable', 'date_format:H:i'];
            $rules["break.$day.end_time"] = ['nullable', 'date_format:H:i'];
            $rules["break.$day.is_active"] = ['nullable', 'boolean'];
        }

        $validator = Validator::make($request->all(), $rules);

        // فاليديشين إضافي مخصص بعد القواعد الأساسية
        $validator->after(function ($validator) use ($request, $days) {

            // ✅ تحقق من ترتيب أوقات العمل / الاستراحة لكل يوم
            foreach ($days as $day) {
                $work = $request->input("work.$day", []);
                $break = $request->input("break.$day", []);

                $workActive = !empty($work['is_active']);
                $workStart = $work['start_time'] ?? null;
                $workEnd = $work['end_time'] ?? null;

                // لو اليوم نشط لازم start و end
                if ($workActive) {
                    if (!$workStart || !$workEnd) {
                        $validator->errors()->add(
                            "work.$day.start_time",
                            __('validation.custom.work_day_missing', [], app()->getLocale())
                            ?? 'Work start/end time is required for active day.'
                        );
                    } elseif ($workEnd <= $workStart) {
                        $validator->errors()->add(
                            "work.$day.end_time",
                            app()->getLocale() === 'ar'
                            ? 'وقت نهاية الدوام يجب أن يكون بعد وقت البداية.'
                            : 'Work end time must be after start time.'
                        );
                    }
                }

                // الاستراحة (اختيارية، لكن لو محددة نتأكد من صحتها)
                $breakActive = !empty($break['is_active']);
                $breakStart = $break['start_time'] ?? null;
                $breakEnd = $break['end_time'] ?? null;

                if ($breakActive || $breakStart || $breakEnd) {
                    if (!$breakStart || !$breakEnd) {
                        $validator->errors()->add(
                            "break.$day.start_time",
                            app()->getLocale() === 'ar'
                            ? 'يجب تحديد وقت بداية ونهاية الاستراحة عند تفعيلها.'
                            : 'Break start and end time are required when break is active or set.'
                        );
                    } elseif ($breakEnd <= $breakStart) {
                        $validator->errors()->add(
                            "break.$day.end_time",
                            app()->getLocale() === 'ar'
                            ? 'وقت نهاية الاستراحة يجب أن يكون بعد وقت البداية.'
                            : 'Break end time must be after start time.'
                        );
                    }

                    // تأكد أن الاستراحة داخل وقت العمل (إذا اليوم نشط)
                    if ($workActive && $workStart && $workEnd && $breakStart && $breakEnd) {
                        if (!($breakStart >= $workStart && $breakEnd <= $workEnd)) {
                            $validator->errors()->add(
                                "break.$day.start_time",
                                app()->getLocale() === 'ar'
                                ? 'وقت الاستراحة يجب أن يكون داخل فترة الدوام.'
                                : 'Break interval must be within working interval.'
                            );
                        }
                    }
                }
            }

            // ✅ تحقق من الـ polygon لو موجود
            $polyStr = $request->input('work_area_polygon');
            if ($polyStr) {
                $coords = json_decode($polyStr, true);

                if (!is_array($coords)) {
                    $validator->errors()->add(
                        'work_area_polygon',
                        app()->getLocale() === 'ar'
                        ? 'صيغة منطقة العمل غير صحيحة.'
                        : 'Invalid work area polygon format.'
                    );
                    return;
                }

                if (count($coords) < 3) {
                    $validator->errors()->add(
                        'work_area_polygon',
                        app()->getLocale() === 'ar'
                        ? 'يجب أن يحتوي المضلع على 3 نقاط على الأقل.'
                        : 'Polygon must contain at least 3 points.'
                    );
                    return;
                }

                foreach ($coords as $index => $pt) {
                    if (
                        !isset($pt['lat'], $pt['lng']) ||
                        !is_numeric($pt['lat']) || !is_numeric($pt['lng'])
                    ) {
                        $validator->errors()->add(
                            'work_area_polygon',
                            app()->getLocale() === 'ar'
                            ? 'إحداثيات منطقة العمل غير صحيحة.'
                            : 'Invalid coordinates in work area polygon.'
                        );
                        break;
                    }
                }
            }
        });

        // يرمي ValidationException لو في أخطاء
        $data = $validator->validate();

        // ✅ كل شيء تمام، نبدأ التخزين
        $employee = null;

        DB::transaction(function () use (&$employee, $data, $request, $days) {

            // 1) إنشاء المستخدم (biker)
            $userPayload = [
                'name' => $data['name'],
                'mobile' => $data['mobile'],
                'email' => $data['email'] ?? null,
                'password' => Hash::make($data['password']),
                'user_type' => 'biker',
                'birth_date' => $data['birth_date'] ?? null,
                'gender' => $data['gender'],
                'is_active' => $request->boolean('is_active', true),
                'notification' => $request->boolean('notification', true),
            ];

            /** @var \App\Models\User $user */
            $user = User::create($userPayload);

            // 2) إنشاء الموظف
            $employee = Employee::create([
                'user_id' => $user->id,
                'is_active' => $user->is_active,
            ]);

            // 3) ساعات العمل الأسبوعية (employee_weekly_intervals)
            // نمسح أي سجل سابق احتياطاً (ما في بس للسلامة)
            EmployeeWeeklyInterval::where('employee_id', $employee->id)->delete();

            $workInput = $request->input('work', []);
            $breakInput = $request->input('break', []);

            $intervalRows = [];

            foreach ($days as $day) {
                $w = $workInput[$day] ?? [];
                $b = $breakInput[$day] ?? [];

                // WORK
                $workActive = !empty($w['is_active']);
                $workStart = $w['start_time'] ?? null;
                $workEnd = $w['end_time'] ?? null;

                if ($workActive && $workStart && $workEnd) {
                    $intervalRows[] = [
                        'employee_id' => $employee->id,
                        'day' => $day,
                        'type' => 'work',
                        'start_time' => $workStart,
                        'end_time' => $workEnd,
                        'is_active' => true,
                        'created_by' => auth()->id(),
                        'updated_by' => auth()->id(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                // BREAK
                $breakActive = !empty($b['is_active']);
                $breakStart = $b['start_time'] ?? null;
                $breakEnd = $b['end_time'] ?? null;

                if ($breakActive && $breakStart && $breakEnd) {
                    $intervalRows[] = [
                        'employee_id' => $employee->id,
                        'day' => $day,
                        'type' => 'break',
                        'start_time' => $breakStart,
                        'end_time' => $breakEnd,
                        'is_active' => true,
                        'created_by' => auth()->id(),
                        'updated_by' => auth()->id(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            if (!empty($intervalRows)) {
                EmployeeWeeklyInterval::insert($intervalRows);
            }

            // 4) الخدمات (employee_services) - باستخدام Query Builder فقط
            DB::table('employee_services')
                ->where('employee_id', $employee->id)
                ->delete();

            if (!empty($data['services'])) {
                $serviceRows = [];
                foreach ($data['services'] as $serviceId) {
                    $serviceRows[] = [
                        'employee_id' => $employee->id,
                        'service_id' => $serviceId,
                        'is_active' => true,
                        'created_by' => auth()->id(),
                        'updated_by' => auth()->id(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                DB::table('employee_services')->insert($serviceRows);
            }


            // 5) منطقة العمل (employee_work_areas)
            EmployeeWorkArea::where('employee_id', $employee->id)->delete();

            $polyStr = $data['work_area_polygon'] ?? null;
            if ($polyStr) {
                $coords = json_decode($polyStr, true);

                $lats = array_column($coords, 'lat');
                $lngs = array_column($coords, 'lng');

                EmployeeWorkArea::create([
                    'employee_id' => $employee->id,
                    'polygon' => $coords, // لو الـ cast في الموديل JSON
                    'min_lat' => min($lats),
                    'max_lat' => max($lats),
                    'min_lng' => min($lngs),
                    'max_lng' => max($lngs),
                    'is_active' => true,
                    'created_by' => auth()->id(),
                    'updated_by' => auth()->id(),
                ]);
            }
        });

        // 🔁 الرد
        if ($request->ajax()) {
            return response()->json([
                'message' => __('employees.created_successfully'),
                'redirect' => route('dashboard.employees.show', $employee->id),
            ]);
        }

        return redirect()
            ->route('dashboard.employees.show', $employee->id)
            ->with('success', __('employees.created_successfully'));
    }

    public function show(Employee $employee)
    {
        // نحمل كل العلاقات اللي نحتاجها في صفحة الـ show
        $employee->load([
            'user',
            'services',
            'weeklyIntervals',
            'workArea',
        ]);

        // الأيام اللي مستخدمينها في الـ schema
        $days = [
            'saturday',
            'sunday',
            'monday',
            'tuesday',
            'wednesday',
            'thursday',
            'friday',
        ];

        // نرتب السجلات حسب اليوم والنوع (work / break)
        $weeklyByDay = [];

        foreach ($days as $day) {
            $weeklyByDay[$day] = [
                'work' => null,
                'break' => null,
            ];
        }

        foreach ($employee->weeklyIntervals as $interval) {
            $dayKey = $interval->day; // 'monday'..الخ

            if (!array_key_exists($dayKey, $weeklyByDay)) {
                continue;
            }

            $data = [
                'start_time' => $interval->start_time,
                'end_time' => $interval->end_time,
                'is_active' => (bool) $interval->is_active,
            ];

            if ($interval->type === 'work') {
                $weeklyByDay[$dayKey]['work'] = $data;
            } elseif ($interval->type === 'break') {
                $weeklyByDay[$dayKey]['break'] = $data;
            }
        }

        // 🗺️ تجهيز الـ polygon لمنطقة العمل
        $workAreaPolygon = null;

        if ($employee->workArea) {
            $polygon = $employee->workArea->polygon;

            // لو الـ polygon مخزن كـ JSON string
            if (is_string($polygon)) {
                $decoded = json_decode($polygon, true);
                if (is_array($decoded)) {
                    $workAreaPolygon = $decoded;
                }
            } elseif (is_array($polygon)) {
                // لو عندك cast => 'polygon' => 'array' في الموديل
                $workAreaPolygon = $polygon;
            }
        }

        return view('dashboard.employees.show', [
            'employee' => $employee,
            'weeklyByDay' => $weeklyByDay,
            'workAreaPolygon' => $workAreaPolygon,
        ]);
    }

    public function edit(Employee $employee)
    {
        $employee->load([
            'user',
            'services',
            'weeklyIntervals',
            'workArea',
        ]);

        $days = [
            'saturday',
            'sunday',
            'monday',
            'tuesday',
            'wednesday',
            'thursday',
            'friday',
        ];

        $weeklyByDay = [];
        foreach ($days as $day) {
            $weeklyByDay[$day] = [
                'work' => null,
                'break' => null,
            ];
        }

        foreach ($employee->weeklyIntervals as $interval) {
            $dayKey = $interval->day;

            if (!array_key_exists($dayKey, $weeklyByDay)) {
                continue;
            }

            $data = [
                'start_time' => $interval->start_time,
                'end_time' => $interval->end_time,
                'is_active' => (bool) $interval->is_active,
            ];

            if ($interval->type === 'work') {
                $weeklyByDay[$dayKey]['work'] = $data;
            } elseif ($interval->type === 'break') {
                $weeklyByDay[$dayKey]['break'] = $data;
            }
        }

        $services = Service::orderBy('sort_order')->orderBy('id')->get();

        $selectedServiceIds = $employee->services->pluck('id')->all();

        $workAreaPolygonJson = null;
        if ($employee->workArea) {
            $polygon = $employee->workArea->polygon;

            if (is_string($polygon)) {
                $workAreaPolygonJson = $polygon;
            } elseif (is_array($polygon)) {
                $workAreaPolygonJson = json_encode($polygon);
            }
        }

        return view('dashboard.employees.edit', [
            'employee' => $employee,
            'weeklyByDay' => $weeklyByDay,
            'services' => $services,
            'selectedServiceIds' => $selectedServiceIds,
            'workAreaPolygonJson' => $workAreaPolygonJson,
        ]);
    }

    public function update(Request $request, Employee $employee)
    {
        $user = $employee->user;

        $days = [
            'saturday',
            'sunday',
            'monday',
            'tuesday',
            'wednesday',
            'thursday',
            'friday',
        ];

        $rules = [
            'name' => ['required', 'string', 'max:190'],
            'mobile' => [
                'required',
                'string',
                'max:20',
                Rule::unique('users', 'mobile')->ignore($user->id),
            ],
            'email' => [
                'nullable',
                'email',
                'max:190',
                Rule::unique('users', 'email')->ignore($user->id),
            ],

            // كلمة المرور اختيارية
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],

            'birth_date' => ['nullable', 'date'],
            'gender' => ['required', 'in:male,female'],

            'is_active' => ['nullable', 'boolean'],
            'notification' => ['nullable', 'boolean'],

            'services' => ['nullable', 'array'],
            'services.*' => ['integer', 'exists:services,id'],

            'work' => ['nullable', 'array'],
            'break' => ['nullable', 'array'],

            'work_area_polygon' => ['nullable', 'string'],
        ];

        // foreach ($days as $day) {
        //     $rules["work.$day.start_time"] = ['nullable', 'date_format:H:i'];
        //     $rules["work.$day.end_time"] = ['nullable', 'date_format:H:i'];
        //     $rules["work.$day.is_active"] = ['nullable', 'boolean'];

        //     $rules["break.$day.start_time"] = ['nullable', 'date_format:H:i'];
        //     $rules["break.$day.end_time"] = ['nullable', 'date_format:H:i'];
        //     $rules["break.$day.is_active"] = ['nullable', 'boolean'];
        // }

        $validator = Validator::make($request->all(), $rules);

        $validator->after(function ($validator) use ($request, $days) {

            foreach ($days as $day) {
                $work = $request->input("work.$day", []);
                $break = $request->input("break.$day", []);

                $workActive = !empty($work['is_active']);
                $workStart = $work['start_time'] ?? null;
                $workEnd = $work['end_time'] ?? null;

                if ($workActive) {
                    if (!$workStart || !$workEnd) {
                        $validator->errors()->add(
                            "work.$day.start_time",
                            app()->getLocale() === 'ar'
                            ? 'يجب تحديد وقت بداية ونهاية الدوام لليوم النشط.'
                            : 'Work start/end time is required for active day.'
                        );
                    } elseif ($workEnd <= $workStart) {
                        $validator->errors()->add(
                            "work.$day.end_time",
                            app()->getLocale() === 'ar'
                            ? 'وقت نهاية الدوام يجب أن يكون بعد وقت البداية.'
                            : 'Work end time must be after start time.'
                        );
                    }
                }

                $breakActive = !empty($break['is_active']);
                $breakStart = $break['start_time'] ?? null;
                $breakEnd = $break['end_time'] ?? null;

                if ($breakActive || $breakStart || $breakEnd) {
                    if (!$breakStart || !$breakEnd) {
                        $validator->errors()->add(
                            "break.$day.start_time",
                            app()->getLocale() === 'ar'
                            ? 'يجب تحديد وقت بداية ونهاية الاستراحة عند تفعيلها أو تعبئة أحد الحقول.'
                            : 'Break start and end time are required when break is active or set.'
                        );
                    } elseif ($breakEnd <= $breakStart) {
                        $validator->errors()->add(
                            "break.$day.end_time",
                            app()->getLocale() === 'ar'
                            ? 'وقت نهاية الاستراحة يجب أن يكون بعد وقت البداية.'
                            : 'Break end time must be after start time.'
                        );
                    }

                    if ($workActive && $workStart && $workEnd && $breakStart && $breakEnd) {
                        if (!($breakStart >= $workStart && $breakEnd <= $workEnd)) {
                            $validator->errors()->add(
                                "break.$day.start_time",
                                app()->getLocale() === 'ar'
                                ? 'وقت الاستراحة يجب أن يكون داخل فترة الدوام.'
                                : 'Break interval must be within working interval.'
                            );
                        }
                    }
                }
            }

            $polyStr = $request->input('work_area_polygon');
            if ($polyStr) {
                $coords = json_decode($polyStr, true);

                if (!is_array($coords)) {
                    $validator->errors()->add(
                        'work_area_polygon',
                        app()->getLocale() === 'ar'
                        ? 'صيغة منطقة العمل غير صحيحة.'
                        : 'Invalid work area polygon format.'
                    );
                    return;
                }

                if (count($coords) < 3) {
                    $validator->errors()->add(
                        'work_area_polygon',
                        app()->getLocale() === 'ar'
                        ? 'يجب أن يحتوي المضلع على 3 نقاط على الأقل.'
                        : 'Polygon must contain at least 3 points.'
                    );
                    return;
                }

                foreach ($coords as $pt) {
                    if (
                        !isset($pt['lat'], $pt['lng']) ||
                        !is_numeric($pt['lat']) || !is_numeric($pt['lng'])
                    ) {
                        $validator->errors()->add(
                            'work_area_polygon',
                            app()->getLocale() === 'ar'
                            ? 'إحداثيات منطقة العمل غير صحيحة.'
                            : 'Invalid coordinates in work area polygon.'
                        );
                        break;
                    }
                }
            }
        });

        $data = $validator->validate();

        DB::transaction(function () use ($employee, $user, $data, $request, $days) {

            // 1) تحديث المستخدم
            $user->name = $data['name'];
            $user->mobile = $data['mobile'];
            $user->email = $data['email'] ?? null;
            $user->birth_date = $data['birth_date'] ?? null;
            $user->gender = $data['gender'];
            $user->is_active = $request->boolean('is_active', true);
            $user->notification = $request->boolean('notification', true);

            // كلمة المرور لو اترسلت
            if (!empty($data['password'])) {
                $user->password = Hash::make($data['password']);
            }

            // نضمن نوع المستخدم biker
            $user->user_type = 'biker';

            $user->save();

            // 2) تحديث الموظف
            $employee->is_active = $user->is_active;
            $employee->save();

            // 3) ساعات العمل الأسبوعية
            EmployeeWeeklyInterval::where('employee_id', $employee->id)->delete();

            $workInput = $request->input('work', []);
            $breakInput = $request->input('break', []);

            $intervalRows = [];

            foreach ($days as $day) {
                $w = $workInput[$day] ?? [];
                $b = $breakInput[$day] ?? [];

                $workActive = !empty($w['is_active']);
                $workStart = $w['start_time'] ?? null;
                $workEnd = $w['end_time'] ?? null;

                if ($workActive && $workStart && $workEnd) {
                    $intervalRows[] = [
                        'employee_id' => $employee->id,
                        'day' => $day,
                        'type' => 'work',
                        'start_time' => $workStart,
                        'end_time' => $workEnd,
                        'is_active' => true,
                        'created_by' => auth()->id(),
                        'updated_by' => auth()->id(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                $breakActive = !empty($b['is_active']);
                $breakStart = $b['start_time'] ?? null;
                $breakEnd = $b['end_time'] ?? null;

                if ($breakActive && $breakStart && $breakEnd) {
                    $intervalRows[] = [
                        'employee_id' => $employee->id,
                        'day' => $day,
                        'type' => 'break',
                        'start_time' => $breakStart,
                        'end_time' => $breakEnd,
                        'is_active' => true,
                        'created_by' => auth()->id(),
                        'updated_by' => auth()->id(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            if (!empty($intervalRows)) {
                EmployeeWeeklyInterval::insert($intervalRows);
            }

            // 4) الخدمات
            DB::table('employee_services')
                ->where('employee_id', $employee->id)
                ->delete();

            if (!empty($data['services'])) {
                $serviceRows = [];
                foreach ($data['services'] as $serviceId) {
                    $serviceRows[] = [
                        'employee_id' => $employee->id,
                        'service_id' => $serviceId,
                        'is_active' => true,
                        'created_by' => auth()->id(),
                        'updated_by' => auth()->id(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                DB::table('employee_services')->insert($serviceRows);
            }

            // 5) منطقة العمل
            $polyStr = $data['work_area_polygon'] ?? null;

            // نجيب السطر حتى لو كان Soft Deleted
            $workArea = EmployeeWorkArea::withTrashed()
                ->where('employee_id', $employee->id)
                ->first();

            if (!$polyStr || trim($polyStr) === '') {

                // لو ما في مضلع في الفورم → نحذف (soft delete) لو موجود
                if ($workArea && !$workArea->trashed()) {
                    $workArea->delete();
                }

            } else {
                $coords = json_decode($polyStr, true);

                if (!is_array($coords) || count($coords) < 3) {
                    throw new \RuntimeException('Invalid polygon data.');
                }

                $lats = array_column($coords, 'lat');
                $lngs = array_column($coords, 'lng');

                if (!$workArea) {
                    // أول مرة يتخزن للموظف
                    $workArea = new EmployeeWorkArea();
                    $workArea->employee_id = $employee->id;
                    $workArea->created_by = auth()->id();
                } elseif ($workArea->trashed()) {
                    // كان soft deleted → نرجعه
                    $workArea->restore();
                }

                $workArea->polygon = $coords;
                $workArea->min_lat = min($lats);
                $workArea->max_lat = max($lats);
                $workArea->min_lng = min($lngs);
                $workArea->max_lng = max($lngs);
                $workArea->is_active = true;
                $workArea->updated_by = auth()->id();

                $workArea->save();
            }


        });

        if ($request->ajax()) {
            return response()->json([
                'message' => __('employees.updated_successfully'),
                'redirect' => route('dashboard.employees.show', $employee->id),
            ]);
        }

        return redirect()
            ->route('dashboard.employees.show', $employee->id)
            ->with('success', __('employees.updated_successfully'));
    }

    public function destroy(Employee $employee, Request $request)
    {
        $employee->load('user');

        if ($employee->user) {
            $employee->user->delete();
        }
        $employee->delete();

        if ($request->ajax()) {
            return response()->json([
                'message' => __('employees.deleted_successfully'),
            ]);
        }

        return redirect()
            ->route('dashboard.employees.index')
            ->with('success', __('employees.deleted_successfully'));
    }
}