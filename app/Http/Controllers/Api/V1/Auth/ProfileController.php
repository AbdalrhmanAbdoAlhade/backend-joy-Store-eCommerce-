<?php
// app/Http/Controllers/Api/V1/Auth/ProfileController.php
namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\Auth\UpdateProfileRequest;
use App\Http\Resources\Auth\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function update(UpdateProfileRequest $request)
    {
        $user = $request->user();
        $data = $request->validated();

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update($data);

        return response()->json([
            'message' => 'تم تحديث الملف الشخصي',
            'user' => new UserResource($user->fresh()),
        ]);
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        $request->user()->update([
            'password' => $request->password,
        ]);

        return response()->json(['message' => 'تم تغيير كلمة المرور بنجاح']);
    }

    public function destroy(Request $request)
    {
        $user = $request->user();
        $user->tokens()->delete();
        $user->delete();

        return response()->json(['message' => 'تم حذف الحساب']);
    }
}
