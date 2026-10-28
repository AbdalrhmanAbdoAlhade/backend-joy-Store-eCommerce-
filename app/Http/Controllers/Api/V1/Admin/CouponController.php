<?php
// app/Http/Controllers/Api/V1/Admin/CouponController.php
namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCouponRequest;
use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function index(Request $request)
    {
        $coupons = Coupon::latest()->paginate(20);

        return response()->json($coupons);
    }

    public function store(StoreCouponRequest $request)
    {
        $data = $request->validated();
        $data['code'] = strtoupper($data['code']);

        $coupon = Coupon::create($data);

        return response()->json([
            'message' => 'تم إنشاء الكوبون',
            'coupon' => $coupon,
        ], 201);
    }

    public function update(Request $request, Coupon $coupon)
    {
        $request->validate([
            'name' => ['sometimes', 'string'],
            'value' => ['sometimes', 'numeric', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
        ]);

        $coupon->update($request->all());

        return response()->json([
            'message' => 'تم تحديث الكوبون',
            'coupon' => $coupon,
        ]);
    }

    public function destroy(Coupon $coupon)
    {
        $coupon->delete();

        return response()->json(['message' => 'تم حذف الكوبون']);
    }
}
