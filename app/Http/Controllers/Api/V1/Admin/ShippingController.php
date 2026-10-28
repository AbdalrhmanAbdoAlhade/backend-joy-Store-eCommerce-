<?php
// app/Http/Controllers/Api/V1/Admin/ShippingController.php
namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\PickupBranch;
use App\Models\ShippingMethod;
use App\Models\ShippingRate;
use App\Models\ShippingZone;
use Illuminate\Http\Request;

class ShippingController extends Controller
{
    // Zones
    public function zones()
    {
        return response()->json(ShippingZone::with('rates.method')->get());
    }

    public function storeZone(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string'],
            'governorates' => ['required', 'array', 'min:1'],
            'is_active' => ['boolean'],
        ]);

        $zone = ShippingZone::create($data);
        return response()->json(['message' => 'تم الإنشاء', 'zone' => $zone], 201);
    }

    public function updateZone(Request $request, ShippingZone $zone)
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string'],
            'governorates' => ['sometimes', 'array'],
            'is_active' => ['boolean'],
        ]);

        $zone->update($data);
        return response()->json(['message' => 'تم التحديث', 'zone' => $zone]);
    }

    // Rates
    public function storeRate(Request $request)
    {
        $data = $request->validate([
            'shipping_zone_id' => ['required', 'exists:shipping_zones,id'],
            'shipping_method_id' => ['required', 'exists:shipping_methods,id'],
            'price' => ['required', 'numeric', 'min:0'],
            'min_order_amount' => ['nullable', 'numeric', 'min:0'],
            'max_order_amount' => ['nullable', 'numeric'],
            'is_free_above' => ['boolean'],
            'free_above_amount' => ['nullable', 'numeric'],
        ]);

        $rate = ShippingRate::create($data);
        return response()->json(['message' => 'تم إضافة التعريفة', 'rate' => $rate], 201);
    }

    public function updateRate(Request $request, ShippingRate $rate)
    {
        $data = $request->validate([
            'price' => ['sometimes', 'numeric', 'min:0'],
            'min_order_amount' => ['nullable', 'numeric'],
            'is_free_above' => ['boolean'],
            'free_above_amount' => ['nullable', 'numeric'],
        ]);

        $rate->update($data);
        return response()->json(['message' => 'تم التحديث', 'rate' => $rate]);
    }

    // Methods
    public function methods()
    {
        return response()->json(ShippingMethod::orderBy('sort_order')->get());
    }

    // Branches
    public function branches()
    {
        return response()->json(PickupBranch::orderBy('name')->get());
    }

    public function storeBranch(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string'],
            'governorate' => ['required', 'string'],
            'city' => ['required', 'string'],
            'address' => ['required', 'string'],
            'phone' => ['nullable', 'string'],
            'lat' => ['nullable', 'numeric'],
            'lng' => ['nullable', 'numeric'],
            'is_active' => ['boolean'],
        ]);

        $branch = PickupBranch::create($data);
        return response()->json(['message' => 'تم الإنشاء', 'branch' => $branch], 201);
    }

    public function updateBranch(Request $request, PickupBranch $branch)
    {
        $branch->update($request->validate([
            'name' => ['sometimes', 'string'],
            'governorate' => ['sometimes', 'string'],
            'city' => ['sometimes', 'string'],
            'address' => ['sometimes', 'string'],
            'phone' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]));

        return response()->json(['message' => 'تم التحديث', 'branch' => $branch]);
    }
}
