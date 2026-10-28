<?php
// app/Http/Controllers/Api/V1/Staff/OrderController.php
namespace App\Http\Controllers\Api\V1\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\UpdateOrderStatusRequest;
use App\Http\Resources\Order\OrderResource;
use App\Models\Order;
use App\Services\Order\OrderStateMachine;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(protected OrderStateMachine $stateMachine) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', Order::class);

        $query = Order::with(['user', 'items'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return OrderResource::collection($query->paginate(20));
    }

    public function show(Order $order)
    {
        $this->authorize('view', $order);

        $order->load(['items', 'statusHistories.changer', 'user', 'payments']);

        return new OrderResource($order);
    }

    public function updateStatus(UpdateOrderStatusRequest $request, Order $order)
    {
        $data = $request->validated();

        if (!empty($data['tracking_number'])) {
            $order->update([
                'tracking_number' => $data['tracking_number'],
                'tracking_url' => $data['tracking_url'] ?? null,
                'shipping_company' => $data['shipping_company'] ?? null,
            ]);
        }

        $order = $this->stateMachine->transition(
            $order,
            $data['status'],
            $request->user(),
            $data['note'] ?? null
        );

        return response()->json([
            'message' => 'تم تحديث حالة الطلب',
            'order' => new OrderResource($order->load('statusHistories')),
        ]);
    }
}
