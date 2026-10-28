<?php
// app/Http/Controllers/Api/V1/Admin/UserController.php
namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Auth\UserResource;
use App\Models\User;
use App\Enums\UserStatus;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('roles')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn ($q) => $q->where('name', 'like', "%{$s}%")
                ->orWhere('email', 'like', "%{$s}%")
                ->orWhere('phone', 'like', "%{$s}%"));
        }

        return UserResource::collection($query->paginate(20));
    }

    public function show(User $user)
    {
        return new UserResource($user->load('roles', 'addresses', 'orders'));
    }

    public function updateStatus(Request $request, User $user)
    {
        $request->validate([
            'status' => ['required', 'in:active,banned,pending'],
        ]);

        $user->update(['status' => $request->status]);

        return response()->json([
            'message' => 'تم تحديث حالة المستخدم',
            'user' => new UserResource($user),
        ]);
    }

    public function destroy(User $user)
    {
        if ($user->isAdmin()) {
            return response()->json(['message' => 'لا يمكن حذف الأدمن'], 403);
        }

        $user->delete();

        return response()->json(['message' => 'تم حذف المستخدم']);
    }
}
