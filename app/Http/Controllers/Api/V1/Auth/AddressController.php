<?php
// app/Http/Controllers/Api/V1/Auth/AddressController.php
namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\AddressRequest;
use App\Http\Resources\Auth\AddressResource;
use App\Models\Address;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function index(Request $request)
    {
        $addresses = $request->user()->addresses()->latest()->get();

        return AddressResource::collection($addresses);
    }

    public function store(AddressRequest $request)
    {
        $user = $request->user();

        if ($request->boolean('is_default')) {
            $user->addresses()->update(['is_default' => false]);
        }

        $address = $user->addresses()->create($request->validated());

        return response()->json([
            'message' => 'تم إضافة العنوان',
            'address' => new AddressResource($address),
        ], 201);
    }

    public function update(AddressRequest $request, Address $address)
    {
        $this->authorize('update', $address); // أو تحقق يدوي

        if ($address->user_id !== $request->user()->id) {
            return response()->json(['message' => 'غير مصرح'], 403);
        }

        if ($request->boolean('is_default')) {
            $request->user()->addresses()->update(['is_default' => false]);
        }

        $address->update($request->validated());

        return response()->json([
            'message' => 'تم تحديث العنوان',
            'address' => new AddressResource($address),
        ]);
    }

    public function destroy(Request $request, Address $address)
    {
        if ($address->user_id !== $request->user()->id) {
            return response()->json(['message' => 'غير مصرح'], 403);
        }

        $address->delete();

        return response()->json(['message' => 'تم حذف العنوان']);
    }

    public function setDefault(Request $request, Address $address)
    {
        if ($address->user_id !== $request->user()->id) {
            return response()->json(['message' => 'غير مصرح'], 403);
        }

        $request->user()->addresses()->update(['is_default' => false]);
        $address->update(['is_default' => true]);

        return response()->json([
            'message' => 'تم تعيين العنوان الافتراضي',
            'address' => new AddressResource($address),
        ]);
    }
}
