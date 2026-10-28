<?php
// app/Http/Controllers/Api/V1/Staff/InventoryController.php
namespace App\Http\Controllers\Api\V1\Staff;

use App\Http\Controllers\Controller;
use App\Models\ProductVariant;
use App\Models\InventoryMovement;
use App\Services\Stock\StockService;
use App\Enums\InventoryMovementType;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function __construct(protected StockService $stock) {}

    public function index(Request $request)
    {
        $query = ProductVariant::with('product:id,name,sku')
            ->orderBy('stock');

        if ($request->boolean('low_stock')) {
            $query->whereRaw('(stock - reserved_stock) <= low_stock_threshold');
        }

        return response()->json($query->paginate(30));
    }

    public function adjust(Request $request, ProductVariant $variant)
    {
        $request->validate([
            'quantity' => ['required', 'integer'], // موجب أو سالب
            'reason' => ['required', 'string', 'max:500'],
        ]);

        if ($request->quantity > 0) {
            $this->stock->increase(
                $variant,
                $request->quantity,
                InventoryMovementType::Adjustment,
                null,
                $request->user(),
                $request->reason
            );
        } else {
            $this->stock->decrease(
                $variant,
                abs($request->quantity),
                InventoryMovementType::Adjustment,
                null,
                $request->user(),
                $request->reason
            );
        }

        return response()->json([
            'message' => 'تم تعديل المخزون',
            'variant' => $variant->fresh(),
        ]);
    }

    public function movements(Request $request, ProductVariant $variant)
    {
        $movements = $variant->inventoryMovements()
            ->with('user:id,name')
            ->latest()
            ->paginate(30);

        return response()->json($movements);
    }
}
