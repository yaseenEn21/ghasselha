<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Yajra\DataTables\DataTables;

class RoleController extends Controller
{

    protected $title;
    protected $page_title;

    public function __construct()
    {
        // 🔐 Permissions middleware
        $this->middleware('can:roles.view')->only(['index', 'show']);
        $this->middleware('can:roles.create')->only(['create', 'store']);
        $this->middleware('can:roles.edit')->only(['edit', 'update']);
        $this->middleware('can:roles.delete')->only(['destroy']);

        $this->title = t("roles.list");

        $this->page_title = t("roles.title");

        view()->share([
            "title" => $this->title,
            "page_title" => $this->page_title,
        ]);

    }

    public function index(DataTables $datatable, Request $request)
    {
        if ($request->ajax()) {
            $query = Role::query()->withCount('permissions');

            // فلترة بالاسم (اختياري)
            if ($request->filled('search_name')) {
                $search = trim($request->get('search_name'));
                $query->where('name', 'like', "%{$search}%");
            }

            return $datatable->eloquent($query)
                ->addColumn('created_at', fn($row) => $row->created_at?->format('Y-m-d'))
                // داخل index() أثناء بناء DataTables
                ->addColumn('actions', function ($row) {
                    // 🚫 لا تعرض أي أزرار للـ admin
                    if (strtolower($row->name) === 'admin') {
                        // تقدر ترجع نص بسيط أو حتى فاضي
                        return '<span class="text-muted">غير قابل للتعديل</span>';
                        // أو لو بدك ولا إشي حرفياً:
                        // return '';
                    }

                    $editUrl = route('dashboard.roles.edit', $row->id);
                    $deleteUrl = route('dashboard.roles.destroy', $row->id);

                    $btns = '';

                    if (auth()->user()->can('roles.edit')) {
                        $btns .= '<a href="' . $editUrl . '" class="btn btn-sm btn-light-primary me-2">تعديل</a>';
                    }

                    if (auth()->user()->can('roles.delete')) {
                        $btns .= '<button type="button" class="btn btn-sm btn-light-danger js-delete"
            data-url="' . $deleteUrl . '" data-name="' . e($row->name) . '">حذف</button>';
                    }

                    return $btns ?: '<span class="text-muted">لا يوجد</span>';
                })
                ->rawColumns(['actions'])
                ->make(true);
        }

        return view('dashboard.role.index');
    }

    public function create()
    {
        $this->title = t("roles.create_new");
        $this->page_title = t("roles.create_new");

        // نجمع الصلاحيات بحسب الوحدة (قبل النقطة)
        $permissions = Permission::all()->groupBy(function ($p) {
            return explode('.', $p->name)[0];
        });

        view()->share([
            "title" => $this->page_title,
            "page_title" => $this->page_title,
        ]);

        return view('dashboard.role.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|unique:roles,name',
            'permissions' => 'array'
        ]);

        $role = Role::create(['name' => $data['name'], 'guard_name' => 'web']);
        $role->syncPermissions($data['permissions'] ?? []);
        return redirect()->route('dashboard.roles.index')->with('success', 'تم إنشاء الدور وتعيين صلاحياته');
    }

    public function edit($id)
    {
        $role = Role::findOrFail($id);

        // جميع الصلاحيات مجمّعة حسب الوحدة (قبل النقطة)
        $permissions = Permission::all()->groupBy(function ($p) {
            return explode('.', $p->name)[0];
        });

        // أسماء الصلاحيات الحالية للدور
        $rolePermissions = $role->permissions->pluck('name')->toArray();

        return view('dashboard.role.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|unique:roles,name,' . $role->id,
            'permissions' => 'array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        $role->update(['name' => $data['name']]);
        $role->syncPermissions($data['permissions'] ?? []);

        return redirect()->route('dashboard.roles.index')->with('success', 'تم تحديث الدور وصلاحياته.');
    }

    public function destroy(Request $request, $id)
    {
        $role = Role::findOrFail($id);
        if (in_array($role->name, ['admin'])) {
            return $request->ajax()
                ? response()->json(['message' => 'لا يمكن حذف هذا الدور.'], 422)
                : back()->with('error', 'لا يمكن حذف هذا الدور.');
        }

        $role->delete();

        return $request->ajax()
            ? response()->json(['message' => 'تم الحذف بنجاح.'])
            : redirect()->route('dashboard.roles.index')->with('success', 'تم الحذف.');
    }

}
