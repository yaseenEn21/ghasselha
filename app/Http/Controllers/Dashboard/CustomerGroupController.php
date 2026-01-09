<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\CustomerGroup;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\DataTables;

class CustomerGroupController extends Controller
{
    protected string $title;
    protected string $page_title;

    public function __construct()
    {
        // عدّل الصلاحيات حسب نظامك
        $this->middleware('can:customer_groups.view')->only(['index', 'datatable']);
        $this->middleware('can:customer_groups.create')->only(['create', 'store']);
        $this->middleware('can:customer_groups.edit')->only(['edit', 'update']);
        $this->middleware('can:customer_groups.delete')->only(['destroy']);

        $this->title = __('customer_groups.title');
        $this->page_title = __('customer_groups.title');

        view()->share([
            'title' => $this->title,
            'page_title' => $this->page_title,
        ]);
    }

    public function index()
    {
        return view('dashboard.customer_groups.index');
    }

    public function datatable(DataTables $datatable, Request $request)
    {
        $query = CustomerGroup::query()
            ->select('customer_groups.*')
            ->withCount('servicePrices')
            ->latest('id');

        if ($search = trim((string) $request->get('search_custom'))) {
            $query->where('name', 'like', "%{$search}%");
        }

        if ($status = $request->get('status')) {
            if ($status === 'active')
                $query->where('is_active', true);
            if ($status === 'inactive')
                $query->where('is_active', false);
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->get('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->get('to'));
        }

        return $datatable->eloquent($query)
            ->addColumn('is_active_badge', function (CustomerGroup $row) {
                return $row->is_active
                    ? '<span class="badge badge-light-success">' . e(__('customer_groups.active')) . '</span>'
                    : '<span class="badge badge-light-danger">' . e(__('customer_groups.inactive')) . '</span>';
            })
            ->addColumn('prices_count', function (CustomerGroup $row) {
                return (int) ($row->service_prices_count ?? 0);
            })
            ->addColumn('created_at_label', function (CustomerGroup $row) {
                return $row->created_at ? $row->created_at->format('Y-m-d') : '—';
            })
            ->addColumn('actions', function (CustomerGroup $row) {
                return view('dashboard.customer_groups._actions', compact('row'))->render();
            })
            ->rawColumns(['is_active_badge', 'actions'])
            ->make(true);
    }

    public function show(CustomerGroup $customerGroup)
    {
        view()->share([
            'title' => __('customer_groups.show'),
            'page_title' => __('customer_groups.show'),
        ]);

        $customerGroup->loadCount('servicePrices');

        return view('dashboard.customer_groups.show', compact('customerGroup'));
    }

    public function create()
    {
        view()->share([
            'title' => __('customer_groups.create'),
            'page_title' => __('customer_groups.create'),
        ]);

        return view('dashboard.customer_groups.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateGroup($request);

        $group = CustomerGroup::create([
            'name' => trim((string) $data['name']),
            'is_active' => $request->boolean('is_active', true),
            'created_by' => $request->user()?->id,
            'updated_by' => $request->user()?->id,
        ]);

        return $request->ajax()
            ? response()->json([
                'message' => __('customer_groups.created_successfully'),
                'redirect' => route('dashboard.customer-groups.index'),
                'data' => ['id' => $group->id],
            ])
            : redirect()->route('dashboard.customer-groups.index')->with('success', __('customer_groups.created_successfully'));
    }

    public function edit(CustomerGroup $customer_group)
    {
        view()->share([
            'title' => __('customer_groups.edit'),
            'page_title' => __('customer_groups.edit'),
        ]);

        return view('dashboard.customer_groups.edit', ['group' => $customer_group]);
    }

    public function update(Request $request, CustomerGroup $customer_group)
    {
        $data = $this->validateGroup($request, $customer_group->id);

        $customer_group->update([
            'name' => trim((string) $data['name']),
            'is_active' => $request->boolean('is_active', true),
            'updated_by' => $request->user()?->id,
        ]);

        return $request->ajax()
            ? response()->json([
                'message' => __('customer_groups.updated_successfully'),
                'redirect' => route('dashboard.customer-groups.index'),
                'data' => ['id' => $customer_group->id],
            ])
            : redirect()->route('dashboard.customer-groups.index')->with('success', __('customer_groups.updated_successfully'));
    }

    public function destroy(Request $request, CustomerGroup $customer_group)
    {
        $customer_group->update(['updated_by' => $request->user()?->id]);
        $customer_group->delete();

        return $request->ajax()
            ? response()->json(['message' => __('customer_groups.deleted_successfully')])
            : redirect()->route('dashboard.customer-groups.index')->with('success', __('customer_groups.deleted_successfully'));
    }

    private function validateGroup(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name' => [
                'required',
                'string',
                'max:190',
                Rule::unique('customer_groups', 'name')->ignore($ignoreId),
            ],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }
}