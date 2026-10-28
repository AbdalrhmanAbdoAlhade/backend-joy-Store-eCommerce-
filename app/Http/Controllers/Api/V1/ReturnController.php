<?php
// app/Http/Controllers/Api/V1/ReturnController.php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ReturnRequest;
use App\Models\ReturnItem;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ReturnController extends Controller
{
    public function index(Request $request)
    {
        $returns = $request->user()
            ->returns() // لازم تضيف relation في User model
            ->with(['order', 'items.orderItem'])
            ->latest()
            ->paginate(15);

        return response()->json($returns);
    }

    public function store(Request $request)
    {
        $request->validate([
            'order_id' => ['required', 'exists:orders,id'],
            'reason' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'images' => ['nullable', 'array', 'max:5'],
            'images.*' => ['image', 'max:2048'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.order_item_id' => ['required', 'exists:order_items,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        $order = Order::where('id', $request->order_id)
            ->where('user_id', $request->user()->id)
            ->where('status', \App\Enums\OrderStatus::Delivered)
            ->firstOrFail();

        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imagePaths[] = $image->store('returns', 'public');
            }
        }

        $return = ReturnRequest::create([
            'return_number' => 'RET-' . strtoupper(Str::random(8)),
            'order_id' => $order->id,
            'user_id' => $request->user()->id,
            'reason' => $request->reason,
            'description' => $request->description,
            'images' => $imagePaths,
            'status' => \App\Enums\ReturnStatus::Pending,
        ]);

        foreach ($request->items as $itemData) {
            ReturnItem::create([
                'return_id' => $return->id,
                'order_item_id' => $itemData['order_item_id'],
                'quantity' => $itemData['quantity'],
            ]);
        }

        return response()->json([
            'message' => 'تم إرسال طلب المرتجع بنجاح',
            'return' => $return->load('items'),
        ], 201);
    }

    public function show(Request $request, ReturnRequest $return)
    {
        $this->authorize('view', $return);

        $return->load(['order', 'items.orderItem', 'reviewer']);

        return response()->json($return);
    }
}
