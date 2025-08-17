<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\Customer;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Jobs\ProcessOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['customer', 'items.product']);

        // Filtry
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($q) use ($search) {
                    $q->where('company_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $orders = $query->latest()->paginate(20);

        return view('orders.index', compact('orders'));
    }

    public function create()
    {
        $customers = Customer::active()->orderBy('company_name')->get();
        $products = Product::active()->with('category')->get();
        
        return view('orders.create', compact('customers', 'products'));
    }

    public function store(StoreOrderRequest $request)
    {
        DB::beginTransaction();
        
        try {
            $customer = Customer::findOrFail($request->customer_id);
            
            // Sprawdź limit kredytowy
            $orderTotal = $this->calculateOrderTotal($request->items);
            if (!$customer->canPlaceOrder($orderTotal)) {
                return back()->with('error', 'Przekroczony limit kredytowy klienta.');
            }

            // Utwórz zamówienie
            $order = Order::create([
                'customer_id' => $customer->id,
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'payment_method' => $request->payment_method,
                'shipping_address' => $request->shipping_address,
                'billing_address' => $request->billing_address ?? $request->shipping_address,
                'notes' => $request->notes,
                'shipping_cost' => $request->shipping_cost ?? 0,
                'discount_amount' => 0,
            ]);

            // Dodaj pozycje zamówienia
            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                
                // Sprawdź dostępność
                if ($product->stock_quantity < $item['quantity']) {
                    throw new \Exception("Niewystarczająca ilość produktu: {$product->name}");
                }

                $unitPrice = $product->getPriceForCustomer($customer);
                
                $orderItem = $order->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $unitPrice,
                    'discount_percent' => $item['discount'] ?? 0,
                    'tax_rate' => 23, // VAT
                ]);

                $orderItem->createProductSnapshot();
                $orderItem->save();

                // Zmniejsz stan magazynowy
                $product->decrementStock($item['quantity']);
            }

            // Przelicz totale
            $order->calculateTotals();

            // Aktualizuj bilans klienta
            $customer->updateBalance($order->total_amount);
            $customer->update(['last_order_at' => now()]);

            // Wyślij do kolejki do przetworzenia
            ProcessOrder::dispatch($order);

            DB::commit();

            return redirect()->route('orders.show', $order)
                ->with('success', 'Zamówienie zostało utworzone pomyślnie.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Błąd: ' . $e->getMessage())->withInput();
        }
    }

    public function show(Order $order)
    {
        $order->load(['customer', 'items.product.category']);
        return view('orders.show', compact('order'));
    }

public function edit(Order $order)
{
    if (!in_array($order->status, ['pending', 'processing'])) {
        return redirect()->route('orders.show', $order)
            ->with('warning', 'To zamówienie może mieć ograniczone możliwości edycji ze względu na status.');
    }

    $customers = Customer::active()->orderBy('company_name')->get();
    $products = Product::active()->with('category')->get();
    
    return view('orders.edit', compact('order', 'customers', 'products'));
}

// Zaktualizuj metodę update
public function update(Request $request, Order $order)
{
    // Walidacja podstawowa
    $validated = $request->validate([
        'status' => 'required|in:pending,processing,shipped,delivered,cancelled',
        'payment_status' => 'required|in:unpaid,paid,refunded',
        'payment_method' => 'required|in:transfer,card,cash,deferred',
        'notes' => 'nullable|string|max:500',
        'shipping_cost' => 'required|numeric|min:0',
        'discount_amount' => 'required|numeric|min:0',
    ]);

    DB::beginTransaction();
    
    try {
        // Zapisz stary status
        $oldStatus = $order->status;
        
        // Aktualizuj podstawowe dane zamówienia
        $order->update($validated);

        // Jeśli zamówienie jest w statusie pozwalającym na edycję produktów
        if (in_array($order->status, ['pending', 'processing']) && $request->has('items')) {
            // Walidacja pozycji
            $request->validate([
                'items' => 'array',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.unit_price' => 'required|numeric|min:0',
            ]);

            // Przywróć stany magazynowe dla starych pozycji
            foreach ($order->items as $oldItem) {
                if ($oldItem->product) {
                    $oldItem->product->incrementStock($oldItem->quantity);
                }
            }

            // Usuń stare pozycje
            $order->items()->delete();

            // Dodaj nowe pozycje
            foreach ($request->items as $itemData) {
                if (empty($itemData['product_id'])) {
                    continue;
                }

                $product = Product::findOrFail($itemData['product_id']);
                
                // Sprawdź dostępność
                if ($product->stock_quantity < $itemData['quantity']) {
                    throw new \Exception("Niewystarczająca ilość produktu: {$product->name}. Dostępne: {$product->stock_quantity}");
                }

                // Utwórz nową pozycję
                $orderItem = $order->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'discount_percent' => 0,
                    'tax_rate' => 23,
                ]);

                $orderItem->createProductSnapshot();
                $orderItem->save();

                // Zmniejsz stan magazynowy
                $product->decrementStock($itemData['quantity']);
            }
        }

        // Przelicz totale
        $order->calculateTotals();

        // Jeśli zmienił się status na anulowany
        if ($oldStatus !== 'cancelled' && $order->status === 'cancelled') {
            // Przywróć stany magazynowe
            foreach ($order->items as $item) {
                if ($item->product) {
                    $item->product->incrementStock($item->quantity);
                }
            }
        }

        DB::commit();

        return redirect()->route('orders.show', $order)
            ->with('success', 'Zamówienie zostało zaktualizowane.');
            
    } catch (\Exception $e) {
        DB::rollBack();
        return back()->with('error', 'Błąd podczas aktualizacji: ' . $e->getMessage())->withInput();
    }
}

    public function destroy(Order $order)
    {
        if ($order->cancel()) {
            return redirect()->route('orders.index')
                ->with('success', 'Zamówienie zostało anulowane.');
        }

        return back()->with('error', 'Nie można anulować tego zamówienia.');
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled'
        ]);

        $oldStatus = $order->status;
        $order->status = $request->status;
        $order->save();

        // Log zmiany statusu (bez Spatie)
        Log::info('Status zamówienia zmieniony', [
            'order_id'   => $order->id,
            'order_no'   => $order->order_number,
            'old_status' => $oldStatus,
            'new_status' => $request->status,
            'user_id'    => optional($request->user())->id,
        ]);

        return redirect()->route('orders.show', $order)
            ->with('success', 'Status zamówienia został zaktualizowany.');
    }

public function invoice(Order $order)
{
    $order->load(['customer', 'items.product']);
    
    // Opcja 1: Zwróć widok HTML do wydruku
    return view('orders.invoice', compact('order'));
    
    // Opcja 2: Jeśli chcesz PDF, zainstaluj pakiet:
    // composer require barryvdh/laravel-dompdf
    // 
    // Następnie użyj:
    // $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('orders.invoice', compact('order'));
    // return $pdf->download("faktura-{$order->order_number}.pdf");
}

    private function calculateOrderTotal($items)
    {
        $total = 0;
        foreach ($items as $item) {
            $product = Product::find($item['product_id']);
            if ($product) {
                $price = $product->price * $item['quantity'];
                $discount = $price * (($item['discount'] ?? 0) / 100);
                $subtotal = $price - $discount;
                $tax = $subtotal * 0.23; // VAT
                $total += $subtotal + $tax;
            }
        }
        return $total;
    }
}
