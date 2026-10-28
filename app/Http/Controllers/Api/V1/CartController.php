<?php
// app/Http/Controllers/Api/V1/CartController.php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cart\AddToCartRequest;
use App\Http\Requests\Cart\UpdateCartItemRequest;
use App\Http\Resources\Cart\CartResource;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\Pricing\PricingService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(protected PricingService $pricing) {}

    public function index(Request $request)
    {
        $cart = $this->getOrCreateCart($request);

        $cart->load(['items.product.primaryImage', 'items.variant']);

        return new CartResource($cart);
    }

    public function store(AddToCartRequest $request)
    {
        $cart = $this->getOrCreateCart($request);
        $product = Product::active()->findOrFail($request->product_id);
        $variant = $request->product_variant_id
            ? ProductVariant::where('product_id', $product->id)->findOrFail($request->product_variant_id)
            : $product->variants()->active()->first();

        $price = $this->pricing->getProductPrice($product, $variant);

        $item = $cart->items()
            ->where('product_id', $product->id)
            ->where('product_variant_id', $variant?->id)
            ->first();

        if ($item) {
            $item->update([
                'quantity' => $item->quantity + $request->quantity,
                'price' => $price,
            ]);
        } else {
            $cart->items()->create([
                'product_id' => $product->id,
                'product_variant_id' => $variant?->id,
                'quantity' => $request->quantity,
                'price' => $price,
            ]);
        }

        $cart->load(['items.product.primaryImage', 'items.variant']);

        return response()->json([
            'message' => 'تمت الإضافة إلى السلة',
            'cart' => new CartResource($cart),
        ]);
    }

    public function update(UpdateCartItemRequest $request, CartItem $item)
    {
        $cart = $this->getOrCreateCart($request);

        if ($item->cart_id !== $cart->id) {
            return response()->json(['message' => 'غير مصرح'], 403);
        }

        $item->update(['quantity' => $request->quantity]);

        $cart->load(['items.product.primaryImage', 'items.variant']);

        return new CartResource($cart);
    }

    public function destroy(Request $request, CartItem $item)
    {
        $cart = $this->getOrCreateCart($request);

        if ($item->cart_id !== $cart->id) {
            return response()->json(['message' => 'غير مصرح'], 403);
        }

        $item->delete();

        $cart->load(['items.product.primaryImage', 'items.variant']);

        return new CartResource($cart);
    }

    public function clear(Request $request)
    {
        $cart = $this->getOrCreateCart($request);
        $cart->items()->delete();

        return response()->json(['message' => 'تم تفريغ السلة']);
    }

    protected function getOrCreateCart(Request $request): Cart
    {
        if ($request->user()) {
            return Cart::firstOrCreate(['user_id' => $request->user()->id]);
        }

        $sessionId = $request->header('X-Session-Id') ?? session()->getId();

        return Cart::firstOrCreate(['session_id' => $sessionId]);
    }
}
