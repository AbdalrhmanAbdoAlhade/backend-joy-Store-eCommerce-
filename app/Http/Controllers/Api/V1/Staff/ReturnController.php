<?php
// app/Http/Controllers/Api/V1/Staff/ReturnController.php
namespace App\Http\Controllers\Api\V1\Staff;

use App\Http\Controllers\Controller;
use App\Models\ReturnRequest;
use App\Services\Stock\StockService;
use App\Enums\ReturnStatus;
use App\Enums\InventoryMovementType;
use Illuminate\Http\Request;

class ReturnController extends Controller
{
    public function __construct(protected StockService $stock) {}

    public function index(Request $request)
    {
        $this->authorize('manage', ReturnRequest::class);

        $query = ReturnRequest::with(['user:id,name', 'order', 'items'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return response()->json($query->paginate(20));
    }

    public function show(ReturnRequest $return)
    {
        $this->authorize('manage', $return);

        $return->load(['user', 'order.items', 'items.orderItem', 'reviewer']);

        return response()->json($return);
    }

    public function approve(Request $request, ReturnRequest $return)
    {
        $this->authorize('manage', $return);

        $request->validate([
            'admin_note' => ['nullable', 'string'],
            'restock' => ['boolean'],
            'refund_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $return->update([
            'status' => ReturnStatus::Approved,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
            'admin_note' => $request->admin_note,
            'restock' => $request->boolean('restock', true),
            'refund_amount' => $request->refund_amount,
        ]);

        return response()->json([
            'message' => 'تم الموافقة على المرتجع',
            'return' => $return,
        ]);
    }

    public function reject(Request $request, ReturnRequest $return)
    {
        $this->authorize('manage', $return);

        $request->validate(['admin_note' => ['required', 'string']]);

        $return->update([
            'status' => ReturnStatus::Rejected,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
            'admin_note' => $request->admin_note,
        ]);

        return response()->json([
            'message' => 'تم رفض المرتجع',
            'return' => $return,
        ]);
    }

    public function receive(Request $request, ReturnRequest $return)
    {
        $this->authorize('manage', $return);

        if ($return->status !== ReturnStatus::Approved) {
            return response()->json(['message' => 'يجب الموافقة أولاً'], 422);
        }

        $return->update(['status' => ReturnStatus::Received]);

        // إرجاع المخزون لو مطلوب
        if ($return->restock) {
            foreach ($return->items as $item) {
                $variant = $item->orderItem->variant;
                if ($variant) {
                    $this->stock->increase(
                        $variant,
                        $item->quantity,
                        InventoryMovementType::Return,
                        $return,
                        $request->user(),
                        "مرتجع #{$return->return_number}"
                    );
                }
            }
        }

        return response()->json([
            'message' => 'تم استلام المرتجع' . ($return->restock ? ' وإرجاع المخزون' : ''),
            'return' => $return,
        ]);
    }
}
