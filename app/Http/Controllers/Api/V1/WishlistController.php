<?php
// app/Http/Controllers/Api/V1/WishlistController.php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Catalog\ProductResource;
use App\Models\Product;
use App\Models\Wishlist;
use App\Models\WishlistItem;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index(Request $request)
    {
        $wishlist = $this->getOrCreateWishlist($request);
        $wishlist->load(['items.product.primaryImage', 'items.variant']);

        return response()->json([
            'items' => $wishlist->items->map(fn ($item) => [
                'id' => $item->id,
                'product' => new ProductResource($item->product),
                'variant_id' => $item->product_variant_id,
            ]),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'product_variant_id' => ['nullable', 'exists:product_variants,id'],
        ]);

        $wishlist = $this->getOrCreateWishlist($request);

        $exists = $wishlist->items()
            ->where('product_id', $request->product_id)
            ->where('product_variant_id', $request->product_variant_id)
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'المنتج موجود بالفعل في المفضلة'], 422);
        }

        $wishlist->items()->create([
            'product_id' => $request->product_id,
            'product_variant_id' => $request->product_variant_id,
        ]);

        return response()->json(['message' => 'تمت الإضافة إلى المفضلة'], 201);
    }

    public function destroy(Request $request, WishlistItem $item)
    {
        $wishlist = $this->getOrCreateWishlist($request);

        if ($item->wishlist_id !== $wishlist->id) {
            return response()->json(['message' => 'غير مصرح'], 403);
        }

        $item->delete();

        return response()->json(['message' => 'تم الحذف من المفضلة']);
    }

    public function moveToCart(Request $request, WishlistItem $item)
    {
        $wishlist = $this->getOrCreateWishlist($request);

        if ($item->wishlist_id !== $wishlist->id) {
            return response()->json(['message' => 'غير مصرح'], 403);
        }

        // إضافة للسلة
        $cartController = app(CartController::class);
        $addRequest = new \App\Http\Requests\Cart\AddToCartRequest();
        $addRequest->merge([
            'product_id' => $item->product_id,
            'product_variant_id' => $item->product_variant_id,
            'quantity' => 1,
        ]);
        $addRequest->setUserResolver(fn () => $request->user());

        $cartController->store($addRequest);
        $item->delete();

        return response()->json(['message' => 'تم نقل المنتج إلى السلة']);
    }

    protected function getOrCreateWishlist(Request $request): Wishlist
    {
        return Wishlist::firstOrCreate(['user_id' => $request->user()->id]);
    }
}
