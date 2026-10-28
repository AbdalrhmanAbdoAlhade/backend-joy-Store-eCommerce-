<?php
// app/Http/Controllers/Api/V1/CheckoutController.php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Checkout\CheckoutRequest;
use App\Http\Resources\Order\OrderResource;
use App\Models\Cart;
use App\Services\Checkout\CheckoutService;
use App\Services\Payment\PaymentManager;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function __construct(
        protected CheckoutService $checkout,
        protected PaymentManager $paymentManager,
    ) {}

    public function store(CheckoutRequest $request)
    {
        $cart = $this->getCart($request);

        if ($cart->items->isEmpty()) {
            return response()->json(['message' => 'السلة فارغة'], 422);
        }

        $order = $this->checkout->process($cart, $request->validated(), $request->user());

        // بدء الدفع
        $driver = $this->paymentManager->driver($request->payment_method);
        $payment = $driver->charge($order, [
            'idempotency_key' => $request->idempotency_key,
        ]);

        return response()->json([
            'message' => 'تم إنشاء الطلب بنجاح',
            'order' => new OrderResource($order->load('items')),
            'payment' => [
                'id' => $payment->id,
                'gateway' => $payment->gateway,
                'status' => $payment->status,
                'gateway_data' => $payment->gateway_response, // iframe url لـ Paymob
            ],
        ], 201);
    }

    protected function getCart(Request $request): Cart
    {
        if ($request->user()) {
            return Cart::with('items.product', 'items.variant')
                ->where('user_id', $request->user()->id)
                ->firstOrFail();
        }

        $sessionId = $request->header('X-Session-Id');
        return Cart::with('items.product', 'items.variant')
            ->where('session_id', $sessionId)
            ->firstOrFail();
    }
}
