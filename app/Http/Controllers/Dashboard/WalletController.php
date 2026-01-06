<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class WalletController extends Controller
{
    protected string $title;
    protected string $page_title;

    public function __construct()
    {
        $this->middleware('can:wallets.view')->only(['index']);
        $this->middleware('can:wallets.create')->only(['create', 'store', 'users', 'walletInfo']);

        $this->title = __('wallets.list');
        $this->page_title = __('wallets.title');

        view()->share([
            'title' => $this->title,
            'page_title' => $this->page_title,
        ]);
    }

    public function index()
    {
        return view('dashboard.wallets.index');
    }

    public function datatable(Request $request)
    {
        $locale = app()->getLocale();

        $query = WalletTransaction::query()
            ->with(['user', 'wallet'])
            ->select('wallet_transactions.*');

        // Filters
        if ($request->filled('direction')) {
            $query->where('direction', $request->direction);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        return DataTables::of($query)
            ->addColumn('user', function (WalletTransaction $t) {
                $name = $t->user?->name ?? '-';
                $mobile = $t->user?->mobile ?? '';
                return trim(($mobile ? $mobile . ' - ' : '') . $name);
            })
            ->addColumn('direction_badge', function (WalletTransaction $t) {
                $isCredit = $t->direction === 'credit';
                $label = $isCredit ? __('wallets.directions.credit') : __('wallets.directions.debit');
                $class = $isCredit ? 'badge-light-success' : 'badge-light-danger';
                return '<span class="badge ' . $class . '">' . e($label) . '</span>';
            })
            ->addColumn('type_label', function (WalletTransaction $t) {
                return __('wallets.types.' . $t->type);
            })
            ->addColumn('amount_formatted', function (WalletTransaction $t) {
                return number_format((float)$t->amount, 2);
            })
            ->addColumn('balance_before_formatted', function (WalletTransaction $t) {
                return number_format((float)$t->balance_before, 2);
            })
            ->addColumn('balance_after_formatted', function (WalletTransaction $t) {
                return number_format((float)$t->balance_after, 2);
            })
            ->addColumn('description_localized', function (WalletTransaction $t) use ($locale) {
                $desc = $t->description ?? null;
                if (is_array($desc)) {
                    return $desc[$locale] ?? ($desc['ar'] ?? ($desc['en'] ?? '—'));
                }
                return $desc ?: '—';
            })
            ->addColumn('reference', function (WalletTransaction $t) {
                if (!$t->referenceable_type || !$t->referenceable_id) return '—';
                return class_basename($t->referenceable_type) . ' #' . $t->referenceable_id;
            })
            ->editColumn('created_at', function (WalletTransaction $t) {
                return $t->created_at?->format('Y-m-d H:i');
            })
            ->rawColumns(['direction_badge'])
            ->orderColumn('id', 'id $1')
            ->make(true);
    }

    public function create()
    {
        return view('dashboard.wallets.create');
    }

    public function store(Request $request, WalletService $walletService)
    {
        $allowedTypes = [
            'credit' => ['topup', 'refund', 'adjustment'],
            'debit'  => ['booking_charge', 'package_purchase', 'adjustment'],
        ];

        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'direction' => ['required', 'in:credit,debit'],
            'type' => ['required', 'string'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'description_ar' => ['nullable', 'string', 'max:500'],
            'description_en' => ['nullable', 'string', 'max:500'],
            'meta' => ['nullable', 'array'],
        ]);

        if (!in_array($data['type'], $allowedTypes[$data['direction']] ?? [], true)) {
            return response()->json([
                'message' => __('wallets.validation.invalid_type_for_direction'),
            ], 422);
        }

        $user = User::findOrFail($data['user_id']);
        $actorId = auth()->id();

        // وصف متعدد لغات
        $descAr = trim((string)($data['description_ar'] ?? ''));
        $descEn = trim((string)($data['description_en'] ?? ''));

        $description = null;
        if ($descAr !== '' || $descEn !== '') {
            // لو المستخدم كتب واحد فقط انسخه للثاني
            if ($descAr === '' && $descEn !== '') $descAr = $descEn;
            if ($descEn === '' && $descAr !== '') $descEn = $descAr;

            $description = ['ar' => $descAr, 'en' => $descEn];
        }

        $meta = $data['meta'] ?? null;

        try {
            if ($data['direction'] === 'credit') {
                $walletService->credit(
                    user: $user,
                    amount: (float)$data['amount'],
                    type: $data['type'],
                    description: $description,
                    referenceable: null,
                    paymentId: null,
                    actorId: $actorId,
                    meta: $meta
                );
            } else {
                $walletService->debit(
                    user: $user,
                    amount: (float)$data['amount'],
                    type: $data['type'],
                    description: $description,
                    referenceable: null,
                    paymentId: null,
                    actorId: $actorId,
                    meta: $meta
                );
            }

            return response()->json([
                'message' => __('wallets.created_successfully'),
                'redirect' => route('dashboard.wallets.index'),
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(), // Insufficient wallet balance
            ], 422);
        }
    }

    public function walletInfo(User $user, WalletService $walletService)
    {
        $wallet = Wallet::where('user_id', $user->id)->first();
        if (!$wallet) {
            $wallet = $walletService->getOrCreateWallet($user);
        }

        $totalCredit = WalletTransaction::where('user_id', $user->id)->where('direction', 'credit')->sum('amount');
        $totalDebit  = WalletTransaction::where('user_id', $user->id)->where('direction', 'debit')->sum('amount');

        return response()->json([
            'balance' => (float)$wallet->balance,
            'currency' => $wallet->currency,
            'is_active' => (bool)$wallet->is_active,
            'total_credit' => (float)$totalCredit,
            'total_debit' => (float)$totalDebit,
        ]);
    }
}
