<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Repositories\Contracts\UserRepositoryInterface;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    protected $userRepo;
    protected $title;
    protected $page_title;

    public function __construct(UserRepositoryInterface $userRepo)
    {

        // 🔐 Permissions middleware
        $this->middleware('can:users.view')->only(['index', 'show']);
        $this->middleware('can:users.create')->only(['create', 'store']);
        $this->middleware('can:users.edit')->only(['edit', 'update']);
        $this->middleware('can:users.delete')->only(['destroy']);

        $this->userRepo = $userRepo;

        $this->title = t("users.list");

        $this->page_title = t("users.title");

        view()->share([
            "title" => $this->title,
            "page_title" => $this->page_title,
        ]);

    }

    public function select2(Request $request, $userType = 'customer')
    {
        $q = trim((string)$request->get('q', ''));
        $page = max(1, (int)$request->get('page', 1));
        $perPage = max(1, min(50, (int)$request->get('per_page', 10)));
        $skip = ($page - 1) * $perPage;

        $query = User::query()->where('user_type', $userType)->select(['id', 'name', 'mobile']);

        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                  ->orWhere('mobile', 'like', "%{$q}%");
            });
        }

        $total = (clone $query)->count();

        $users = $query
            ->orderByDesc('id')
            ->skip($skip)
            ->take($perPage)
            ->get();

        $results = $users->map(function ($u) {
            $text = trim(($u->mobile ? $u->mobile . ' - ' : '') . ($u->name ?? ''));
            return ['id' => $u->id, 'text' => $text ?: ('#' . $u->id)];
        })->values();

        return response()->json([
            'results' => $results,
            'more' => ($skip + $perPage) < $total,
        ]);
    }

    public function index(DataTables $datatable, Request $request)
    {
        if ($request->ajax()) {
            $query = $this->userRepo->all(); // يرجع Eloquent\Builder

            $query->where('user_type', 'admin');

            if ($request->filled('search_name')) {
                $search = $request->string('search_name')->trim();
                $query->where('name', 'like', "%{$search}%");
            }

            $currentUserId = auth()->id();

            return $datatable->eloquent($query)
                ->addColumn('creator_name', fn($row) => $row->createdBy?->name ?? '—')
                ->addColumn('is_active_badge', function ($row) {
                    return $row->is_active
                        ? '<span class="badge badge-light-success">مفعل</span>'
                        : '<span class="badge badge-light-danger">غير مفعل</span>';
                })
                ->addColumn('role_name', function ($row) {
                    return $row->roles->pluck('name')->implode(', ');
                })
                ->addColumn('actions', function ($row) use ($currentUserId) {
                    $editUrl = route('dashboard.users.edit', $row->id);
                    $deleteUrl = route('dashboard.users.destroy', $row->id);

                    $html = '<a href="' . $editUrl . '" class="btn btn-sm btn-light-warning me-1">تعديل</a>';

                    // ✅ لا تظهر زر الحذف لو هذا المستخدم هو نفسه المستخدم الحالي
                    if ($row->id != $currentUserId) {
                        $html .= '
                        <button type="button" class="btn btn-sm btn-light-danger"
                            onclick="if(confirm(\'هل أنت متأكد من الحذف؟\')) {
                                document.getElementById(\'delete-user-' . $row->id . '\').submit();
                            }">
                            حذف
                        </button>
                        <form id="delete-user-' . $row->id . '" action="' . $deleteUrl . '" method="POST" style="display:none;">
                            ' . csrf_field() . method_field('DELETE') . '
                        </form>
                    ';
                    }

                    return $html;
                })
                ->editColumn('created_at', fn($row) => $row->created_at?->format('Y-m-d'))
                ->rawColumns(['created_at', 'creator_name', 'is_active_badge', 'actions'])
                ->make(true);
        }

        return view('dashboard.user.index');
    }

    public function create()
    {
        $this->title = t("users.create_new");
        $this->page_title = t("users.create_new");

        view()->share([
            "title" => $this->page_title,
            "page_title" => $this->page_title,
        ]);

        $roles = Role::pluck('name', 'id');
        return view('dashboard.user.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:190'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'mobile' => ['required', 'regex:/^05[0-9]{8}$/', 'unique:users,mobile'],
            'password' => ['required', 'string', 'min:6'],
            'role_id' => ['required', 'exists:roles,id'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'name.required' => 'حقل الاسم مطلوب.',
            'email.required' => 'حقل البريد الإلكتروني مطلوب.',
            'email.email' => 'صيغة البريد الإلكتروني غير صحيحة.',
            'email.unique' => 'هذا البريد الإلكتروني مستخدم من قبل.',
            'mobile.required' => 'رقم الجوال مطلوب.',
            'mobile.regex' => 'رقم الجوال يجب أن يبدأ بـ 05 ويتكون من 10 أرقام.',
            'mobile.unique' => 'رقم الجوال مستخدم من قبل.',
            'password.required' => 'كلمة المرور مطلوبة.',
            'password.min' => 'كلمة المرور يجب أن لا تقل عن 6 أحرف.',
            'role_id.required' => 'يجب اختيار دور للمستخدم.',
            'role_id.exists' => 'الدور المحدد غير صالح.',
        ]);

        $payload = [
            'name' => $data['name'],
            'email' => $data['email'],
            'mobile' => $data['mobile'],
            'password' => bcrypt($data['password']),
            'is_active' => $request->boolean('is_active', true),
            'user_type' => 'admin',
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ];

        $user = $this->userRepo->create($payload);

        $role = Role::find($data['role_id']);
        $user->assignRole($role);

        return redirect()
            ->route('dashboard.users.index')
            ->with('success', 'تم إنشاء المستخدم وتعيين دوره بنجاح.');
    }

    public function show($id)
    {
        $user = $this->userRepo->find($id);
        return view('dashboard.user.show', compact('user'));
    }

    public function edit($id)
    {
        $user = $this->userRepo->find($id);
        $roles = Role::pluck('name', 'id');

        return view('dashboard.user.edit', compact('user', 'roles'));
    }

    public function update(Request $request, $id)
    {
        $user = $this->userRepo->find($id);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:190'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email,' . $user->id],
            'mobile' => ['required', 'regex:/^05[0-9]{8}$/', 'unique:users,mobile,' . $user->id],
            'password' => ['nullable', 'string', 'min:6'],
            'role_id' => ['required', 'exists:roles,id'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'name.required' => 'حقل الاسم مطلوب.',
            'email.required' => 'حقل البريد الإلكتروني مطلوب.',
            'email.email' => 'صيغة البريد الإلكتروني غير صحيحة.',
            'email.unique' => 'هذا البريد الإلكتروني مستخدم من قبل.',
            'mobile.required' => 'رقم الجوال مطلوب.',
            'mobile.regex' => 'رقم الجوال يجب أن يبدأ بـ 05 ويتكون من 10 أرقام.',
            'mobile.unique' => 'رقم الجوال مستخدم من قبل.',
            'password.min' => 'كلمة المرور يجب أن لا تقل عن 6 أحرف.',
            'role_id.required' => 'يجب اختيار دور للمستخدم.',
            'role_id.exists' => 'الدور المحدد غير صالح.',
        ]);

        $payload = [
            'name' => $data['name'],
            'email' => $data['email'],
            'mobile' => $data['mobile'],
            'is_active' => $request->boolean('is_active', true),
        ];

        if (!empty($data['password'])) {
            $payload['password'] = bcrypt($data['password']);
        }

        $this->userRepo->update($user->id, $payload);

        $role = Role::findOrFail($data['role_id']);
        $user->syncRoles([$role]);

        return redirect()
            ->route('dashboard.users.index')
            ->with('success', 'تم تحديث بيانات المستخدم بنجاح.');
    }

    public function destroy($id)
    {
        $this->userRepo->delete($id);
        return redirect()->route('dashboard.users.index')->with('success', 'تم حذف المستخدم بنجاح');
    }
}
