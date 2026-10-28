<?php
// app/Http/Controllers/Api/V1/Admin/FlashSaleController.php
namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\FlashSale;
use App\Models\FlashSaleProduct;
use Illuminate\Http\Request;

class FlashSaleController extends Controller
{
    public function index()
    {
        return response()->json(
            FlashSale::with('products.product')->latest()->paginate(15)
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'is_active' => ['boolean'],
            'products' => ['required', 'array', 'min:1'],
            'products.*.product_id' => ['required', 'exists:products,id'],
            'products.*.product_variant_id' => ['nullable', 'exists:product_variants,id'],
            'products.*.sale_price' => ['required', 'numeric', 'min:0'],
            'products.*.quantity_limit' => ['nullable', 'integer', 'min:1'],
        ]);

        $flashSale = FlashSale::create([
            'name' => $data['name'],
            'starts_at' => $data['starts_at'],
            'ends_at' => $data['ends_at'],
            'is_active' => $data['is_active'] ?? true,
        ]);

        foreach ($data['products'] as $p) {
            $flashSale->products()->create($p);
        }

        return response()->json([
            'message' => 'تم إنشاء العرض',
            'flash_sale' => $flashSale->load('products.product'),
        ], 201);
    }

    public function update(Request $request, FlashSale $flashSale)
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string'],
            'starts_at' => ['sometimes', 'date'],
            'ends_at' => ['sometimes', 'date'],
            'is_active' => ['boolean'],
        ]);

        $flashSale->update($data);

        return response()->json(['message' => 'تم التحديث', 'flash_sale' => $flashSale]);
    }

    public function destroy(FlashSale $flashSale)
    {
        $flashSale->delete();
        return response()->json(['message' => 'تم الحذف']);
    }
}
