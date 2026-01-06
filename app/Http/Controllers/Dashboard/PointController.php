<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\PointTransaction;
use App\Models\PointWallet;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class PointController extends Controller
{
    protected string $title;
    protected string $page_title;

    public function __construct()
    {
        $this->middleware('can:points.view')->only(['index']);
        $this->middleware('can:points.create')->only(['create', 'store', 'users', 'walletInfo']);

        $this->title = __('points.list');
        $this->page_title = __('points.title');

        view()->share([
            'title' => $this->title,
            'page_title' => $this->page_title,
        ]);
    }

    public function index(DataTables $datatable, Request $request)
    {
        if ($request->ajax()) {
            $query = PointTransaction::query()
                ->with([
                    'user' => function ($q) {
                        $q->select('id', 'name', 'mobile', 'email');
                    }
                ])
                ->select('point_transactions.*');

            // Search by user name/mobile/email
            if ($search = trim((string)$request->get('search_custom'))) {
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('mobile', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            }

            // Filter type
            if ($type = $request->get('type')) {
                if (in_array($type, ['earn', 'redeem', 'adjust', 'refund'])) {
                    $query->where('type', $type);
                }
            }

            // Filter direction: plus/minus
            if ($direction = $request->get('direction')) {
                if ($direction === 'plus') {
                    $query->where('points', '>', 0);
                } elseif ($direction === 'minus') {
                    $query->where('points', '<', 0);
                }
            }

            // Filter archived
            if (($archived = $request->get('archived')) !== null && $archived !== '') {
                if ($archived === '1') {
                    $query->where('is_archived', true);
                } elseif ($archived === '0') {
                    $query->where('is_archived', false);
                }
            }

            // Date range
            if ($dateFrom = $request->get('date_from')) {
                $query->whereDate('created_at', '>=', $dateFrom);
            }
            if ($dateTo = $request->get('date_to')) {
                $query->whereDate('created_at', '<=', $dateTo);
            }

            return $datatable->eloquent($query)
                ->addColumn('user_name', fn(PointTransaction $row) => e($row->user?->name ?? '—'))
                ->addColumn('user_mobile', fn(PointTransaction $row) => e($row->user?->mobile ?? '—'))
                ->addColumn('type_label', function (PointTransaction $row) {
                    return e(__('points.types.' . $row->type));
                })
                ->addColumn('points_badge', function (PointTransaction $row) {
                    $val = (int)$row->points;
                    $isPlus = $val > 0;
                    $text = ($isPlus ? '+' : '') . number_format($val);

                    $cls = $isPlus ? 'badge-light-success' : 'badge-light-danger';
                    if ($val === 0) $cls = 'badge-light';

                    return '<span class="badge ' . $cls . ' fw-semibold">' . e($text) . '</span>';
                })
                ->addColumn('money', function (PointTransaction $row) {
                    if ($row->money_amount === null) return '—';
                    return e(number_format((float)$row->money_amount, 2) . ' ' . ($row->currency ?: ''));
                })
                ->addColumn('reference', function (PointTransaction $row) {
                    if (!$row->reference_type || !$row->reference_id) return '—';
                    return e($row->reference_type . ' #' . $row->reference_id);
                })
                ->editColumn('note', fn(PointTransaction $row) => e($row->note ?? '—'))
                ->editColumn('created_at', fn(PointTransaction $row) => optional($row->created_at)->format('Y-m-d H:i'))
                ->rawColumns(['points_badge'])
                ->make(true);
        }

        return view('dashboard.points.index');
    }

    public function create()
    {
        return view('dashboard.points.create');
    }

    public function walletInfo(User $user)
    {
        $wallet = PointWallet::where('user_id', $user->id)->first();

        return response()->json([
            'balance_points' => (int)($wallet->balance_points ?? 0),
            'total_earned_points' => (int)($wallet->total_earned_points ?? 0),
            'total_spent_points' => (int)($wallet->total_spent_points ?? 0),
        ]);
    }

    public function store(Request $request)
    {
        $rules = [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'action' => ['required', 'in:add,subtract'],
            'points_amount' => ['required', 'integer', 'min:1'],
            'note' => ['nullable', 'string', 'max:500'],
        ];

        $validator = Validator::make($request->all(), $rules);

        $validator->after(function ($validator) use ($request) {
            // لو بدك تمنع الرصيد من النزول تحت الصفر
            $userId = (int)$request->input('user_id');
            $action = $request->input('action');
            $amount = (int)$request->input('points_amount');

            if ($userId && $action === 'subtract' && $amount > 0) {
                $wallet = PointWallet::where('user_id', $userId)->first();
                $balance = (int)($wallet->balance_points ?? 0);

                if ($balance < $amount) {
                    $validator->errors()->add(
                        'points_amount',
                        __('points.validation.insufficient_balance', ['balance' => $balance])
                    );
                }
            }
        });

        $data = $validator->validate();

        DB::transaction(function () use ($data) {
            $userId = (int)$data['user_id'];
            $amount = (int)$data['points_amount'];

            $isAdd = $data['action'] === 'add';

            $signedPoints = $isAdd ? $amount : (-1 * $amount);
            $type = $isAdd ? 'earn' : 'redeem';

            $wallet = PointWallet::firstOrCreate(
                ['user_id' => $userId],
                [
                    'balance_points' => 0,
                    'total_earned_points' => 0,
                    'total_spent_points' => 0,
                ]
            );

            // تحديث المحفظة
            $wallet->balance_points = (int)$wallet->balance_points + $signedPoints;

            if ($signedPoints > 0) {
                $wallet->total_earned_points = (int)$wallet->total_earned_points + $signedPoints;
            } elseif ($signedPoints < 0) {
                $wallet->total_spent_points = (int)$wallet->total_spent_points + abs($signedPoints);
            }

            $wallet->save();

            // إنشاء حركة
            PointTransaction::create([
                'user_id' => $userId,
                'type' => $type,
                'points' => $signedPoints,
                'money_amount' => null,
                'currency' => 'SAR',
                'reference_type' => null,
                'reference_id' => null,
                'note' => $data['note'] ?? null,
                'meta' => [
                    'action' => $data['action'],
                    'balance_after' => (int)$wallet->balance_points,
                ],
                'is_archived' => false,
                'archived_at' => null,
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);
        });

        if ($request->ajax()) {
            return response()->json([
                'message' => __('points.created_successfully'),
                'redirect' => route('dashboard.points.index'),
            ]);
        }

        return redirect()
            ->route('dashboard.points.index')
            ->with('success', __('points.created_successfully'));
    }
}