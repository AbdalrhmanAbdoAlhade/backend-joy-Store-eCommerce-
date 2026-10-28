<?php
// app/Http/Controllers/Api/V1/Admin/RolePermissionController.php
namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionController extends Controller
{
    public function roles()
    {
        return response()->json(
            Role::with('permissions')->get()
        );
    }

    public function permissions()
    {
        return response()->json(Permission::all());
    }

    public function syncRolePermissions(Request $request, Role $role)
    {
        if ($role->name === 'admin') {
            return response()->json(['message' => 'لا يمكن تعديل صلاحيات الأدمن'], 403);
        }

        $data = $request->validate([
            'permissions' => ['required', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $role->syncPermissions($data['permissions']);

        return response()->json([
            'message' => 'تم تحديث صلاحيات الدور',
            'role' => $role->load('permissions'),
        ]);
    }
}
