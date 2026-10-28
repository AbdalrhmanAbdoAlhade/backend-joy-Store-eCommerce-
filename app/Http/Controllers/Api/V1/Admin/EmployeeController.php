<?php
// app/Http/Controllers/Api/V1/Admin/EmployeeController.php
namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Auth\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = User::role(['employee', 'admin'])
            ->with('roles', 'permissions')
            ->get();

        return UserResource::collection($employees);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'unique:users,phone'],
            'password' => ['required', Password::defaults()],
            'role' => ['required', 'in:employee,admin'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => $request->password,
            'is_admin' => $request->role === 'admin',
            'status' => 'active',
        ]);

        $user->assignRole($request->role);

        if ($request->role === 'employee' && $request->permissions) {
            $user->syncPermissions($request->permissions);
        }

        return response()->json([
            'message' => 'تم إنشاء الموظف بنجاح',
            'user' => new UserResource($user->load('roles', 'permissions')),
        ], 201);
    }

    public function updatePermissions(Request $request, User $user)
    {
        $request->validate([
            'permissions' => ['required', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        if ($user->hasRole('admin')) {
            return response()->json(['message' => 'لا يمكن تعديل صلاحيات الأدمن'], 403);
        }

        $user->syncPermissions($request->permissions);

        return response()->json([
            'message' => 'تم تحديث الصلاحيات',
            'user' => new UserResource($user->load('permissions')),
        ]);
    }
}
