<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
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
        $this->userRepo = $userRepo;

        $this->title = t("users.list");

        $this->page_title = t("users.title");

        view()->share([
            "title" => $this->title,
            "page_title" => $this->page_title,
        ]);

    }

    public function index(DataTables $datatable, Request $request)
    {
        if ($request->ajax()) {
            $query = $this->userRepo->all(); // مهم: يرجع Eloquent\Builder

            // فلترة بحسب الاسم
            if ($request->filled('search_name')) {
                $search = $request->string('search_name')->trim();
                $query->where('name', 'like', "%{$search}%");
            }

            return $datatable->eloquent($query)
                ->addColumn('creator_name', fn($row) => $row->createdBy?->name ?? '—')
                ->editColumn('created_at', fn($row) => $row->created_at?->format('Y-m-d'))
                ->rawColumns(['created_at', 'creator_name'])
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
            'name' => 'required|string|max:190',
            'email' => 'required|email|unique:users',
            'mobile' => 'required|unique:users',
            'password' => 'required|min:6',
            'role_id' => 'required|exists:roles,id'
        ]);

        $user = $this->userRepo->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'mobile' => $data['mobile'],
            'password' => bcrypt($data['password']),
        ]);

        $role = Role::find($data['role_id']);
        $user->assignRole($role);
        return redirect()->route('dashboard.users.index')->with('success', 'تم إنشاء المستخدم وتعيين دوره');
    }


    public function show($id)
    {
        $user = $this->userRepo->find($id);
        return view('dashboard.user.show', compact('user'));
    }

    public function edit($id)
    {
        $user = $this->userRepo->find($id);
        return view('dashboard.user.edit', compact('user'));
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|min:6',
        ]);

        if (!empty($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        } else {
            unset($data['password']);
        }

        $this->userRepo->update($id, $data);
        return redirect()->route('dashboard.users.index')->with('success', 'تم تحديث بيانات المستخدم');
    }

    public function destroy($id)
    {
        $this->userRepo->delete($id);
        return redirect()->route('dashboard.users.index')->with('success', 'تم حذف المستخدم بنجاح');
    }
}
