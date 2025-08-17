<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\Customer;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Statystyki ogólne
        $stats = [
            'total_orders' => Order::count(),
            'pending_orders' => Order::pending()->count(),
            'total_customers' => Customer::active()->count(),
            'total_products' => Product::active()->count(),
            'revenue_today' => Order::whereDate('created_at', today())
                ->where('payment_status', 'paid')
                ->sum('total_amount'),
            'revenue_month' => Order::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->where('payment_status', 'paid')
                ->sum('total_amount'),
        ];

        // ALTERNATYWNE ROZWIĄZANIE: Top produkty używając subquery
        $topProductIds = OrderItem::select('product_id')
            ->selectRaw('SUM(quantity) as total_quantity')
            ->groupBy('product_id')
            ->orderByDesc('total_quantity')
            ->limit(5)
            ->pluck('product_id');

        $topProducts = Product::whereIn('id', $topProductIds)
            ->with('category')
            ->get()
            ->map(function ($product) {
                $product->total_sold = OrderItem::where('product_id', $product->id)->sum('quantity');
                return $product;
            })
            ->sortByDesc('total_sold');

        // Ostatnie zamówienia
        $recentOrders = Order::with(['customer', 'items'])
            ->latest()
            ->limit(10)
            ->get();

        // Wykres sprzedaży (ostatnie 30 dni)
        $salesChart = Order::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as orders_count'),
                DB::raw('SUM(total_amount) as total_sales')
            )
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('dashboard.index', compact('stats', 'topProducts', 'recentOrders', 'salesChart'));
    }
}