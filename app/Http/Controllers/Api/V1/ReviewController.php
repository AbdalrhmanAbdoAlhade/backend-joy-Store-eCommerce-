<?php
// app/Http/Controllers/Api/V1/ReviewController.php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request, Product $product)
    {
        $reviews = $product->reviews()
            ->approved()
            ->with('user:id,name,avatar')
            ->latest()
            ->paginate(10);

        return response()->json($reviews);
    }

    public function store(Request $request, Product $product)
    {
        $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'title' => ['nullable', 'string', 'max:255'],
            'body' => ['nullable', 'string', 'max:2000'],
            'order_id' => ['nullable', 'exists:orders,id'],
        ]);

        // التحقق من الشراء (Verified Purchase)
        $isVerified = false;
        if ($request->order_id) {
            $order = Order::where('id', $request->order_id)
                ->where('user_id', $request->user()->id)
                ->where('status', \App\Enums\OrderStatus::Delivered)
                ->whereHas('items', fn ($q) => $q->where('product_id', $product->id))
                ->first();

            $isVerified = (bool) $order;
        }

        $review = Review::updateOrCreate(
            [
                'product_id' => $product->id,
                'user_id' => $request->user()->id,
                'order_id' => $request->order_id,
            ],
            [
                'rating' => $request->rating,
                'title' => $request->title,
                'body' => $request->body,
                'is_verified_purchase' => $isVerified,
                'status' => \App\Enums\ReviewStatus::Pending,
            ]
        );

        return response()->json([
            'message' => 'تم إرسال التقييم بنجاح، سيتم مراجعته قريباً',
            'review' => $review,
        ], 201);
    }

    public function destroy(Request $request, Review $review)
    {
        $this->authorize('delete', $review);

        $review->delete();

        // إعادة حساب متوسط التقييم
        $this->recalculateProductRating($review->product);

        return response()->json(['message' => 'تم حذف التقييم']);
    }

    protected function recalculateProductRating(Product $product): void
    {
        $stats = $product->reviews()->approved()
            ->selectRaw('AVG(rating) as avg_rating, COUNT(*) as count')
            ->first();

        $product->update([
            'avg_rating' => round($stats->avg_rating ?? 0, 2),
            'reviews_count' => $stats->count ?? 0,
        ]);
    }
}
