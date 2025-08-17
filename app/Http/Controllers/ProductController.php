<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\StoreProductRequest;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category');

        // Filtry
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('in_stock')) {
            $query->where('stock_quantity', '>', 0);
        }

        if ($request->filled('featured')) {
            $query->where('is_featured', true);
        }

        // Sortowanie
        $sortBy = $request->get('sort_by', 'name');
        $sortDirection = $request->get('sort_direction', 'asc');
        $query->orderBy($sortBy, $sortDirection);

        $products = $query->paginate(20);
        $categories = Category::active()->orderBy('name')->get();

        return view('products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $categories = Category::active()->orderBy('name')->get();
        return view('products.create', compact('categories'));
    }

    public function store(StoreProductRequest $request)
    {
        $data = $request->validated();
        
        // Generuj SKU jeśli nie podano
        if (empty($data['sku'])) {
            $data['sku'] = $this->generateSku($data['name']);
        }

        // Obsługa uploadu zdjęć
        if ($request->hasFile('images')) {
            $images = [];
            foreach ($request->file('images') as $image) {
                $path = $image->store('products', 'public');
                $images[] = Storage::url($path);
            }
            $data['images'] = $images;
        }

        $product = Product::create($data);

        return redirect()->route('products.show', $product)
            ->with('success', 'Produkt został utworzony pomyślnie.');
    }

    public function show(Product $product)
    {
        $product->load(['category', 'orderItems.order.customer']);
        
        // Statystyki produktu
        $stats = [
            'total_sold' => $product->orderItems->sum('quantity'),
            'total_revenue' => $product->orderItems->sum('total'),
            'orders_count' => $product->orderItems->unique('order_id')->count(),
            'avg_order_quantity' => $product->orderItems->avg('quantity'),
        ];

        // Historia sprzedaży (ostatnie 30 dni)
        $salesHistory = $product->orderItems()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.created_at', '>=', now()->subDays(30))
            ->selectRaw('DATE(orders.created_at) as date, SUM(order_items.quantity) as quantity')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('products.show', compact('product', 'stats', 'salesHistory'));
    }

    public function edit(Product $product)
    {
        $categories = Category::active()->orderBy('name')->get();
        return view('products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'wholesale_price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'min_order_quantity' => 'required|integer|min:1',
            'unit' => 'required|string|max:20',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ]);

        // Obsługa uploadu nowych zdjęć
        if ($request->hasFile('images')) {
            $images = $product->images ?? [];
            foreach ($request->file('images') as $image) {
                $path = $image->store('products', 'public');
                $images[] = Storage::url($path);
            }
            $validated['images'] = $images;
        }

        $product->update($validated);

        return redirect()->route('products.show', $product)
            ->with('success', 'Produkt został zaktualizowany.');
    }

    public function destroy(Product $product)
    {
        // Sprawdź czy produkt nie jest w żadnym zamówieniu
        if ($product->orderItems()->exists()) {
            return back()->with('error', 'Nie można usunąć produktu, który jest w zamówieniach. Możesz go dezaktywować.');
        }

        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Produkt został usunięty.');
    }

    public function toggleFeatured(Product $product)
    {
        $product->update(['is_featured' => !$product->is_featured]);
        
        return back()->with('success', 
            $product->is_featured ? 'Produkt oznaczony jako polecany.' : 'Usunięto oznaczenie polecanego produktu.'
        );
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt,xlsx|max:10240'
        ]);

        // Tutaj logika importu CSV/Excel
        // Można użyć pakietu maatwebsite/excel

        return back()->with('success', 'Import produktów rozpoczęty.');
    }

    public function export(Request $request)
    {
        $products = Product::with('category')->get();
        
        $csv = "SKU,Nazwa,Kategoria,Cena,Stan magazynowy,Aktywny\n";
        foreach ($products as $product) {
            $csv .= "{$product->sku},{$product->name},{$product->category->name},";
            $csv .= "{$product->price},{$product->stock_quantity},";
            $csv .= ($product->is_active ? 'Tak' : 'Nie') . "\n";
        }

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="produkty-' . date('Y-m-d') . '.csv"');
    }

    private function generateSku($name)
    {
        $prefix = strtoupper(substr(Str::slug($name), 0, 3));
        $random = strtoupper(Str::random(4));
        return "{$prefix}-{$random}";
    }
}