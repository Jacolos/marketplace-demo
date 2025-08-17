<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Order;
use App\Models\Product;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderManager extends Component
{
    public $customer_id = '';
    public $items = [];
    public $shippingAddress = [
        'street' => '',
        'city' => '',
        'postal_code' => '',
        'country' => 'PL',
    ];
    public $paymentMethod = 'transfer';
    public $notes = '';
    
    // Dodatkowe właściwości
    public $customers = [];
    public $products = [];

    protected $rules = [
        'customer_id' => 'required|exists:customers,id',
        'items' => 'required|array|min:1',
        'items.*.product_id' => 'required|exists:products,id',
        'items.*.quantity' => 'required|integer|min:1',
        'shippingAddress.street' => 'required|string',
        'shippingAddress.city' => 'required|string',
        'shippingAddress.postal_code' => 'required|string',
        'paymentMethod' => 'required|in:transfer,card,cash,deferred',
    ];

    public function mount()
    {
        $this->loadData();
        $this->addEmptyItem();
    }

    public function loadData()
    {
        $this->customers = Customer::active()->orderBy('company_name')->get();
        $this->products = Product::active()->with('category')->get();
    }

    public function addEmptyItem()
    {
        $this->items[] = [
            'product_id' => '',
            'product_name' => '',
            'quantity' => 1,
            'unit_price' => 0,
            'subtotal' => 0,
        ];
    }

    public function removeItem($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    // PUBLICZNA metoda do obsługi zmiany klienta
    public function changeCustomer()
    {
        if (!empty($this->customer_id)) {
            // Przelicz ceny dla wszystkich produktów
            $this->recalculateAllPrices();
        }
    }

    // PUBLICZNA metoda do obsługi zmiany produktu
    public function changeProduct($index)
    {
        if (isset($this->items[$index]) && !empty($this->items[$index]['product_id'])) {
            $this->updateProductPrice($index, $this->items[$index]['product_id']);
        }
    }

    // PUBLICZNA metoda do obsługi zmiany ilości
    public function changeQuantity($index)
    {
        if (isset($this->items[$index])) {
            $this->calculateItemSubtotal($index);
        }
    }

    // Automatyczna metoda wywoływana przez Livewire przy zmianie customer_id
    public function updatedCustomerId($value)
    {
        if (!empty($value)) {
            $this->recalculateAllPrices();
        }
    }

    // Automatyczna metoda wywoływana przez Livewire przy zmianie items
    public function updatedItems($value, $key)
    {
        // Parsuj klucz, np. "0.product_id" lub "0.quantity"
        $parts = explode('.', $key);
        
        if (count($parts) === 2) {
            $index = (int)$parts[0];
            $field = $parts[1];
            
            if ($field === 'product_id' && !empty($value)) {
                $this->updateProductPrice($index, $value);
            } elseif ($field === 'quantity') {
                $this->calculateItemSubtotal($index);
            }
        }
    }

    // Aktualizuj cenę produktu dla danego indeksu
    private function updateProductPrice($index, $productId)
    {
        $product = Product::find($productId);
        
        if ($product) {
            $price = $this->getProductPrice($product);
            
            $this->items[$index]['product_id'] = $product->id;
            $this->items[$index]['product_name'] = $product->name;
            $this->items[$index]['unit_price'] = $price;
            
            // Zachowaj ilość jeśli już była ustawiona, w przeciwnym razie ustaw 1
            if (empty($this->items[$index]['quantity']) || $this->items[$index]['quantity'] < 1) {
                $this->items[$index]['quantity'] = 1;
            }
            
            $this->calculateItemSubtotal($index);
        }
    }

    // Pobierz cenę produktu dla wybranego klienta
    private function getProductPrice($product)
    {
        if (!empty($this->customer_id)) {
            $customer = Customer::find($this->customer_id);
            if ($customer) {
                // Sprawdź czy metoda istnieje
                if (method_exists($product, 'getPriceForCustomer')) {
                    return $product->getPriceForCustomer($customer);
                } else {
                    // Fallback - oblicz cenę tutaj
                    $price = $product->price;
                    
                    // Jeśli klient jest hurtowy i jest cena hurtowa
                    if ($customer->customer_type === 'wholesale' && $product->wholesale_price) {
                        $price = $product->wholesale_price;
                    }
                    
                    // Zastosuj rabat klienta
                    if ($customer->discount_percent > 0) {
                        $price = $price * (1 - $customer->discount_percent / 100);
                    }
                    
                    return round($price, 2);
                }
            }
        }
        
        return $product->price;
    }

    // Przelicz ceny wszystkich produktów
    private function recalculateAllPrices()
    {
        foreach ($this->items as $index => $item) {
            if (!empty($item['product_id'])) {
                $product = Product::find($item['product_id']);
                if ($product) {
                    $this->items[$index]['unit_price'] = $this->getProductPrice($product);
                    $this->calculateItemSubtotal($index);
                }
            }
        }
    }

    // Oblicz wartość pozycji
    public function calculateItemSubtotal($index)
    {
        if (isset($this->items[$index])) {
            $quantity = (int)($this->items[$index]['quantity'] ?? 0);
            $unitPrice = (float)($this->items[$index]['unit_price'] ?? 0);
            $this->items[$index]['subtotal'] = round($quantity * $unitPrice, 2);
        }
    }

    // Oblicz całkowitą wartość zamówienia (getter dla computed property)
    public function getOrderTotalProperty()
    {
        $subtotal = 0;
        foreach ($this->items as $item) {
            $subtotal += (float)($item['subtotal'] ?? 0);
        }
        
        $tax = $subtotal * 0.23; // VAT 23%
        return round($subtotal + $tax, 2);
    }

    // Zapisz zamówienie
    public function saveOrder()
    {
        // Usuń puste pozycje
        $this->items = array_values(array_filter($this->items, function($item) {
            return !empty($item['product_id']);
        }));
        
        if (empty($this->items)) {
            $this->addError('items', 'Dodaj przynajmniej jeden produkt do zamówienia.');
            return;
        }

        // Sprawdź czy wszystkie pozycje mają ceny
        foreach ($this->items as $index => $item) {
            if (empty($item['unit_price']) || $item['unit_price'] == 0) {
                $this->addError('items.' . $index . '.unit_price', 'Cena produktu nie może być zerowa.');
                return;
            }
        }

        $this->validate();

        DB::beginTransaction();
        
        try {
            $customer = Customer::findOrFail($this->customer_id);
            
            // Sprawdź limit kredytowy
            $orderTotal = $this->orderTotal;
            
            if (!$customer->canPlaceOrder($orderTotal)) {
                throw new \Exception('Przekroczony limit kredytowy klienta.');
            }

            // Utwórz zamówienie
            $order = Order::create([
                'customer_id' => $customer->id,
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'payment_method' => $this->paymentMethod,
                'shipping_address' => $this->shippingAddress,
                'billing_address' => $this->shippingAddress,
                'notes' => $this->notes,
                'shipping_cost' => 0,
                'discount_amount' => 0,
                'subtotal' => 0,
                'tax_amount' => 0,
                'total_amount' => 0,
            ]);

            // Dodaj pozycje zamówienia
            foreach ($this->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                
                // Sprawdź dostępność
                if ($product->stock_quantity < $item['quantity']) {
                    throw new \Exception("Niewystarczająca ilość produktu: {$product->name}. Dostępne: {$product->stock_quantity}");
                }

                // Utwórz pozycję zamówienia
                $orderItem = $order->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount_percent' => 0,
                    'tax_rate' => 23,
                ]);

                // Zapisz snapshot produktu
                $orderItem->createProductSnapshot();
                $orderItem->save();

                // Zmniejsz stan magazynowy
                $product->decrementStock($item['quantity']);
            }

            // Przelicz totale zamówienia
            $order->calculateTotals();
            
            // Aktualizuj bilans klienta
            $customer->updateBalance($order->total_amount);
            $customer->update(['last_order_at' => now()]);

            DB::commit();

            session()->flash('success', 'Zamówienie zostało utworzone pomyślnie.');
            return redirect()->route('orders.show', $order);
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->addError('general', $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.order-manager', [
            'customers' => $this->customers,
            'products' => $this->products,
            'orderTotal' => $this->orderTotal,
        ]);
    }
}