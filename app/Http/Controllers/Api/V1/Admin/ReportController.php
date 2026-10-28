<?php
// app/Http/Controllers/Api/V1/Admin/ReportController.php
namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function dashboard()
    {
        $today = now()->startOfDay();
        $week = now()->startOfWeek();
        $month = now()->startOfMonth();

        return response()->json([
            'sales' => [
                'today' => Order::where('created_at', '>=', $today)->whereNotIn('status', ['cancelled', 'failed'])->sum('total'),
                'week' => Order::where('created_at', '>=', $week)->whereNotIn('status', ['cancelled', 'failed'])->sum('total'),
                'month' => Order::where('created_at', '>=', $month)->whereNotIn('status', ['cancelled', 'failed'])->sum('total'),
            ],
            'orders_count' => [
                'today' => Order::where('created_at', '>=', $today)->count(),
                'pending' => Order::where('status', 'pending')->count(),
                'processing' => Order::whereIn('status', ['confirmed', 'processing', 'ready_to_ship'])->count(),
            ],
            'new_customers' => User::where('created_at', '>=', $month)->count(),
            'avg_order_value' => Order::where('created_at', '>=', $month)
                ->whereNotIn('status', ['cancelled', 'failed'])
                ->avg('total'),
            'top_products' => OrderItem::select('product_id', 'product_name', DB::raw('SUM(quantity) as total_sold'))
                ->groupBy('product_id', 'product_name')
                ->orderByDesc('total_sold')
                ->limit(10)
                ->get(),
            'low_stock' => Product::whereHas('variants', fn ($q) => $q->whereRaw('(stock - reserved_stock) <= low_stock_threshold'))
                ->with('variants')
                ->limit(10)
                ->get(['id', 'name', 'sku']),
        ]);
    }
}
