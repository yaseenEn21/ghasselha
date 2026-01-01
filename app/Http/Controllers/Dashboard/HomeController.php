<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User;
use App\Models\AdmissionApplication;
use Carbon\Carbon;
use App\Models\ChildReport;
use App\Models\ChildReportAnswer;
use App\Models\ReportQuestion;
use Illuminate\Http\Request;
use App\Models\Teacher;
use App\Models\ReportTemplate;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function __construct()
    {
        view()->share([
            'title' => 'لوحة التحكم',
            'page_title' => 'لوحة التحكم',
        ]);
    }

    public function index()
    {
        return view(
            'dashboard.index'
        );
    }

    public function reportsStats(Request $request)
    {
        $date = $request->query('date', now()->toDateString());

        try {
            $targetDate = Carbon::parse($date)->toDateString();
        } catch (\Exception $e) {
            $targetDate = now()->toDateString();
        }

        $totalChildren = Student::count();

        // كل التقارير في هذا اليوم (كل القوالب)
        $baseQuery = ChildReport::query()->whereDate('report_date', $targetDate);

        $totalReports = (clone $baseQuery)->count();

        // عدد الأطفال اللي عندهم تقرير في هذا اليوم
        $childrenWithReport = (clone $baseQuery)
            ->select('child_id')
            ->distinct()
            ->count('child_id');

        $coveragePercent = $totalChildren > 0
            ? round($childrenWithReport * 100 / $totalChildren, 1)
            : 0.0;

        // توزيع "مزاج الطفل اليوم" إن وجد سؤال mood_general
        $moodQuestion = ReportQuestion::where('key', 'mood_general')->first();
        $moodDistribution = [];

        if ($moodQuestion) {
            $moodDistribution = ChildReportAnswer::selectRaw(
                'COALESCE(report_question_options.option_text, report_question_options.option_value, ?) as label,
                 COUNT(*) as total',
                ['غير محدد']
            )
                ->join('child_reports', 'child_reports.id', '=', 'child_report_answers.child_report_id')
                ->leftJoin('report_question_options', 'report_question_options.id', '=', 'child_report_answers.option_id')
                ->whereDate('child_reports.report_date', $targetDate)
                ->where('child_report_answers.question_id', $moodQuestion->id)
                ->groupBy('label')
                ->orderByDesc('total')
                ->get()
                ->toArray();
        }

        return response()->json([
            'success' => true,
            'data' => [
                'date' => $targetDate,
                'total_children' => $totalChildren,
                'total_reports' => $totalReports,
                'children_with_report' => $childrenWithReport,
                'coverage_percent' => $coveragePercent,
                'mood_distribution' => $moodDistribution,
            ],
        ]);
    }

    public function reportsCoverageByTeacher(Request $request)
    {
        $date = $request->query('date', now()->toDateString());

        try {
            $targetDate = Carbon::parse($date)->toDateString();
        } catch (\Exception $e) {
            $targetDate = now()->toDateString();
        }

        // نستخدم فقط التقرير اليومي لو موجود
        $dailyTemplateId = ReportTemplate::where('key', 'daily_report')->value('id');

        // كل المعلمات اللي مربوطين بصف
        $teachers = Teacher::with([
            'user:id,name',
            'classroom:id,name',
        ])
            ->whereNotNull('classroom_id')
            ->get();

        if ($teachers->isEmpty()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'date' => $targetDate,
                    'rows' => [],
                ],
            ]);
        }

        $classroomIds = $teachers->pluck('classroom_id')->unique()->filter()->values();

        // عدد الطلاب في كل صف
        $studentsPerClassroom = Student::select(
            'classroom_id',
            DB::raw('COUNT(*) as total_children')
        )
            ->whereIn('classroom_id', $classroomIds)
            ->groupBy('classroom_id')
            ->pluck('total_children', 'classroom_id');

        // عدد الأطفال اللي عندهم تقرير في هذا اليوم في كل صف
        $reportsPerClassroom = ChildReport::query()
            ->join('students', 'students.id', '=', 'child_reports.child_id')
            ->select(
                'students.classroom_id',
                DB::raw('COUNT(DISTINCT child_reports.child_id) as reported_children')
            )
            ->whereDate('child_reports.report_date', $targetDate)
            ->whereIn('students.classroom_id', $classroomIds)
            ->when($dailyTemplateId, function ($q) use ($dailyTemplateId) {
                $q->where('child_reports.report_template_id', $dailyTemplateId);
            })
            ->groupBy('students.classroom_id')
            ->pluck('reported_children', 'students.classroom_id');

        // بناء الصفوف
        $rows = $teachers->map(function (Teacher $teacher) use ($studentsPerClassroom, $reportsPerClassroom) {
            $classroomId = $teacher->classroom_id;

            $totalChildren = (int) ($studentsPerClassroom[$classroomId] ?? 0);
            $reported = (int) ($reportsPerClassroom[$classroomId] ?? 0);
            $notReported = max($totalChildren - $reported, 0);

            $coverage = $totalChildren > 0
                ? round($reported * 100 / $totalChildren, 1)
                : 0.0;

            return [
                'teacher_name' => $teacher->user?->name ?? '-',
                'classroom_name' => $teacher->classroom?->name ?? '-',
                'total_children' => $totalChildren,
                'reported' => $reported,
                'not_reported' => $notReported,
                'coverage_percent' => $coverage,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => [
                'date' => $targetDate,
                'rows' => $rows,
            ],
        ]);
    }

}
