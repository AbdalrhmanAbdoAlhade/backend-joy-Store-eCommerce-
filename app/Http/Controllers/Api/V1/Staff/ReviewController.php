<?php
// app/Http/Controllers/Api/V1/Staff/ReviewController.php
namespace App\Http\Controllers\Api\V1\Staff;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Product;
use App\Enums\ReviewStatus;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $query = Review::with(['user:id,name', 'product:id,name'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return response()->json($query->paginate(20));
    }

    public function approve(Request $request, Review $review)
    {
        $this->authorize('moderate', $review);

        $review->update([
            'status' => ReviewStatus::Approved,
            'moderated_by' => $request->user()->id,
        ]);

        $this->recalculateProductRating($review->product);

        return response()->json(['message' => 'تم اعتماد التقييم']);
    }

    public function reject(Request $request, Review $review)
    {
        $this->authorize('moderate', $review);

        $review->update([
            'status' => ReviewStatus::Rejected,
            'moderated_by' => $request->user()->id,
        ]);

        return response()->json(['message' => 'تم رفض التقييم']);
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
