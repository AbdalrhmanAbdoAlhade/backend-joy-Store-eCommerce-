<?php
// app/Http/Controllers/Api/V1/OrderController.php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Order\OrderResource;
use App\Models\Order;
use App\Services\Order\OrderStateMachine;
use App\Enums\OrderStatus;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(protected OrderStateMachine $stateMachine) {}

    public function index(Request $request)
    {
        $orders = $request->user()
            ->orders()
            ->with('items')
            ->latest()
            ->paginate(15);

        return OrderResource::collection($orders);
    }

    public function show(Request $request, Order $order)
    {
        $this->authorize('view', $order);

        $order->load(['items', 'statusHistories.changer']);

        return new OrderResource($order);
    }

    public function cancel(Request $request, Order $order)
    {
        $this->authorize('cancel', $order);

        $order = $this->stateMachine->transition(
            $order,
            OrderStatus::Cancelled,
            $request->user(),
            'إلغاء من العميل'
        );

        return response()->json([
            'message' => 'تم إلغاء الطلب',
            'order' => new OrderResource($order),
        ]);
    }

    public function reorder(Request $request, Order $order)
    {
        $this->authorize('view', $order);

        // منطق إعادة الطلب → إضافة المنتجات للسلة
        // ...

        return response()->json(['message' => 'تمت إضافة المنتجات إلى السلة']);
    }
}
