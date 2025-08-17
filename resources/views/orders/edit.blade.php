@extends('layouts.app')

@section('title', 'Edycja zamówienia')

@section('content')
<div class="container mx-auto px-4">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Edycja zamówienia {{ $order->order_number }}</h1>

    @if(!$order->canBeCancelled())
        <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-6">
            <p class="font-bold">Uwaga!</p>
            <p>To zamówienie ma status "{{ $order->status }}" i może mieć ograniczone możliwości edycji.</p>
        </div>
    @endif

    <div class="bg-white rounded-lg shadow p-6">
        <form action="{{ route('orders.update', $order) }}" method="POST" id="orderEditForm">
            @csrf
            @method('PUT')

            <!-- Statusy i płatność -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status zamówienia</label>
                    <select name="status" class="w-full px-3 py-2 border rounded focus:outline-none focus:border-blue-500">
                        <option value="pending" {{ $order->status === 'pending' ? 'selected' : '' }}>Oczekujące</option>
                        <option value="processing" {{ $order->status === 'processing' ? 'selected' : '' }}>W realizacji</option>
                        <option value="shipped" {{ $order->status === 'shipped' ? 'selected' : '' }}>Wysłane</option>
                        <option value="delivered" {{ $order->status === 'delivered' ? 'selected' : '' }}>Dostarczone</option>
                        <option value="cancelled" {{ $order->status === 'cancelled' ? 'selected' : '' }}>Anulowane</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status płatności</label>
                    <select name="payment_status" class="w-full px-3 py-2 border rounded focus:outline-none focus:border-blue-500">
                        <option value="unpaid" {{ $order->payment_status === 'unpaid' ? 'selected' : '' }}>Nieopłacone</option>
                        <option value="paid" {{ $order->payment_status === 'paid' ? 'selected' : '' }}>Opłacone</option>
                        <option value="refunded" {{ $order->payment_status === 'refunded' ? 'selected' : '' }}>Zwrócone</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Metoda płatności</label>
                    <select name="payment_method" class="w-full px-3 py-2 border rounded focus:outline-none focus:border-blue-500">
                        <option value="transfer" {{ $order->payment_method === 'transfer' ? 'selected' : '' }}>Przelew</option>
                        <option value="card" {{ $order->payment_method === 'card' ? 'selected' : '' }}>Karta</option>
                        <option value="cash" {{ $order->payment_method === 'cash' ? 'selected' : '' }}>Gotówka</option>
                        <option value="deferred" {{ $order->payment_method === 'deferred' ? 'selected' : '' }}>Odroczony</option>
                    </select>
                </div>
            </div>

            <!-- Pozycje zamówienia - EDYTOWALNE -->
            @if($order->status === 'pending' || $order->status === 'processing')
            <div class="mb-6">
                <h3 class="text-lg font-semibold mb-3">Pozycje zamówienia</h3>
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                    <p class="text-sm text-yellow-700">
                        <i class="fas fa-info-circle mr-1"></i>
                        Możesz edytować pozycje tylko dla zamówień oczekujących lub w realizacji.
                    </p>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full" id="orderItemsTable">
                        <thead>
                            <tr class="border-b bg-gray-50">
                                <th class="text-left py-2 px-2">Produkt</th>
                                <th class="text-center py-2 px-2">Ilość</th>
                                <th class="text-right py-2 px-2">Cena jedn.</th>
                                <th class="text-right py-2 px-2">Wartość</th>
                                <th class="py-2 px-2">Akcje</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $index => $item)
                            <tr class="border-b order-item-row" data-index="{{ $index }}">
                                <td class="py-2 pr-2">
                                    <select name="items[{{ $index }}][product_id]" 
                                        class="w-full px-2 py-1 border rounded product-select"
                                        data-index="{{ $index }}">
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}" 
                                                data-price="{{ $product->getPriceForCustomer($order->customer) }}"
                                                data-stock="{{ $product->stock_quantity }}"
                                                {{ $item->product_id == $product->id ? 'selected' : '' }}>
                                                {{ $product->name }} ({{ $product->sku }}) - Stan: {{ $product->stock_quantity }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="text-center py-2 px-2">
                                    <input type="number" 
                                        name="items[{{ $index }}][quantity]" 
                                        value="{{ $item->quantity }}"
                                        min="1" 
                                        class="w-20 px-2 py-1 border rounded text-center quantity-input"
                                        data-index="{{ $index }}">
                                </td>
                                <td class="text-right py-2 px-2">
                                    <input type="number" 
                                        name="items[{{ $index }}][unit_price]" 
                                        value="{{ $item->unit_price }}"
                                        step="0.01"
                                        class="w-24 px-2 py-1 border rounded text-right price-input"
                                        data-index="{{ $index }}">
                                </td>
                                <td class="text-right py-2 px-2">
                                    <span class="item-total" data-index="{{ $index }}">
                                        {{ number_format($item->quantity * $item->unit_price, 2) }} zł
                                    </span>
                                </td>
                                <td class="text-center py-2 pl-2">
                                    <button type="button" onclick="removeOrderItem(this)" 
                                        class="text-red-600 hover:text-red-800">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <button type="button" onclick="addNewItem()" class="mt-3 text-blue-600 hover:text-blue-800">
                    <i class="fas fa-plus mr-1"></i> Dodaj nowy produkt
                </button>
            </div>
            @else
            <!-- Tylko podgląd dla innych statusów -->
            <div class="mb-6">
                <h3 class="text-lg font-semibold mb-3">Pozycje zamówienia (tylko podgląd)</h3>
                <table class="min-w-full">
                    <thead>
                        <tr class="border-b bg-gray-50">
                            <th class="text-left py-2">Produkt</th>
                            <th class="text-center py-2">Ilość</th>
                            <th class="text-right py-2">Cena jedn.</th>
                            <th class="text-right py-2">Wartość</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                        <tr class="border-b">
                            <td class="py-2">{{ $item->product->name ?? 'Produkt usunięty' }}</td>
                            <td class="text-center py-2">{{ $item->quantity }} {{ $item->product->unit ?? '' }}</td>
                            <td class="text-right py-2">{{ number_format($item->unit_price, 2) }} zł</td>
                            <td class="text-right py-2">{{ number_format($item->total, 2) }} zł</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif

            <!-- Koszty dodatkowe -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Koszt wysyłki</label>
                    <input type="number" name="shipping_cost" value="{{ $order->shipping_cost }}" 
                        step="0.01" min="0" id="shippingCost"
                        class="w-full px-3 py-2 border rounded focus:outline-none focus:border-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Rabat</label>
                    <input type="number" name="discount_amount" value="{{ $order->discount_amount }}" 
                        step="0.01" min="0" id="discountAmount"
                        class="w-full px-3 py-2 border rounded focus:outline-none focus:border-blue-500">
                </div>
            </div>

            <!-- Uwagi -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Uwagi</label>
                <textarea name="notes" rows="3" 
                    class="w-full px-3 py-2 border rounded focus:outline-none focus:border-blue-500">{{ $order->notes }}</textarea>
            </div>

            <!-- Podsumowanie -->
            <div class="bg-gray-50 p-4 rounded mb-6">
                <div class="text-right">
                    <div class="mb-1">
                        <span class="text-gray-600">Wartość produktów: </span>
                        <span id="subtotalDisplay">{{ number_format($order->items->sum('subtotal'), 2) }}</span> zł
                    </div>
                    <div class="mb-1">
                        <span class="text-gray-600">VAT (23%): </span>
                        <span id="taxDisplay">{{ number_format($order->items->sum('tax_amount'), 2) }}</span> zł
                    </div>
                    <div class="mb-1">
                        <span class="text-gray-600">Koszt wysyłki: </span>
                        <span id="shippingDisplay">{{ number_format($order->shipping_cost, 2) }}</span> zł
                    </div>
                    <div class="mb-1">
                        <span class="text-gray-600">Rabat: </span>
                        -<span id="discountDisplay">{{ number_format($order->discount_amount, 2) }}</span> zł
                    </div>
                    <div class="text-lg font-bold mt-2 pt-2 border-t">
                        <span>Razem do zapłaty: </span>
                        <span id="totalDisplay" class="text-blue-600">{{ number_format($order->total_amount, 2) }}</span> zł
                    </div>
                </div>
            </div>

            <!-- Przyciski -->
            <div class="flex justify-end gap-4">
                <a href="{{ route('orders.show', $order) }}" 
                    class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400">
                    Anuluj
                </a>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    <i class="fas fa-save mr-2"></i>Zapisz zmiany
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Licznik dla nowych pozycji
let newItemIndex = {{ $order->items->count() }};
const products = @json($products);
const customerId = {{ $order->customer_id }};

// Funkcja dodawania nowej pozycji
function addNewItem() {
    const tbody = document.querySelector('#orderItemsTable tbody');
    const newRow = document.createElement('tr');
    newRow.className = 'border-b order-item-row';
    newRow.dataset.index = newItemIndex;
    
    let productOptions = '';
    products.forEach(product => {
        productOptions += `<option value="${product.id}" 
            data-price="${product.price}" 
            data-stock="${product.stock_quantity}">
            ${product.name} (${product.sku}) - Stan: ${product.stock_quantity}
        </option>`;
    });
    
    newRow.innerHTML = `
        <td class="py-2 pr-2">
            <select name="items[${newItemIndex}][product_id]" 
                class="w-full px-2 py-1 border rounded product-select"
                data-index="${newItemIndex}"
                onchange="updatePrice(this)">
                <option value="">-- Wybierz produkt --</option>
                ${productOptions}
            </select>
        </td>
        <td class="text-center py-2 px-2">
            <input type="number" 
                name="items[${newItemIndex}][quantity]" 
                value="1"
                min="1" 
                class="w-20 px-2 py-1 border rounded text-center quantity-input"
                data-index="${newItemIndex}"
                onchange="calculateItemTotal(${newItemIndex})">
        </td>
        <td class="text-right py-2 px-2">
            <input type="number" 
                name="items[${newItemIndex}][unit_price]" 
                value="0"
                step="0.01"
                class="w-24 px-2 py-1 border rounded text-right price-input"
                data-index="${newItemIndex}"
                onchange="calculateItemTotal(${newItemIndex})">
        </td>
        <td class="text-right py-2 px-2">
            <span class="item-total" data-index="${newItemIndex}">0,00 zł</span>
        </td>
        <td class="text-center py-2 pl-2">
            <button type="button" onclick="removeOrderItem(this)" 
                class="text-red-600 hover:text-red-800">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    `;
    
    tbody.appendChild(newRow);
    newItemIndex++;
}

// Funkcja usuwania pozycji
function removeOrderItem(button) {
    if (confirm('Czy na pewno chcesz usunąć tę pozycję?')) {
        button.closest('tr').remove();
        calculateOrderTotal();
    }
}

// Aktualizacja ceny przy wyborze produktu
function updatePrice(select) {
    const selectedOption = select.options[select.selectedIndex];
    const index = select.dataset.index;
    const priceInput = document.querySelector(`.price-input[data-index="${index}"]`);
    
    if (selectedOption.value) {
        const price = selectedOption.dataset.price;
        priceInput.value = price;
        calculateItemTotal(index);
    }
}

// Obliczanie wartości pozycji
function calculateItemTotal(index) {
    const quantity = parseFloat(document.querySelector(`.quantity-input[data-index="${index}"]`).value) || 0;
    const price = parseFloat(document.querySelector(`.price-input[data-index="${index}"]`).value) || 0;
    const total = quantity * price;
    
    document.querySelector(`.item-total[data-index="${index}"]`).textContent = 
        total.toFixed(2).replace('.', ',') + ' zł';
    
    calculateOrderTotal();
}

// Obliczanie całkowitej wartości zamówienia
function calculateOrderTotal() {
    let subtotal = 0;
    
    // Sumuj wartości wszystkich pozycji
    document.querySelectorAll('.order-item-row').forEach(row => {
        const index = row.dataset.index;
        const quantity = parseFloat(document.querySelector(`.quantity-input[data-index="${index}"]`)?.value) || 0;
        const price = parseFloat(document.querySelector(`.price-input[data-index="${index}"]`)?.value) || 0;
        subtotal += quantity * price;
    });
    
    const tax = subtotal * 0.23;
    const shipping = parseFloat(document.getElementById('shippingCost').value) || 0;
    const discount = parseFloat(document.getElementById('discountAmount').value) || 0;
    const total = subtotal + tax + shipping - discount;
    
    // Aktualizuj wyświetlane wartości
    document.getElementById('subtotalDisplay').textContent = subtotal.toFixed(2).replace('.', ',');
    document.getElementById('taxDisplay').textContent = tax.toFixed(2).replace('.', ',');
    document.getElementById('shippingDisplay').textContent = shipping.toFixed(2).replace('.', ',');
    document.getElementById('discountDisplay').textContent = discount.toFixed(2).replace('.', ',');
    document.getElementById('totalDisplay').textContent = total.toFixed(2).replace('.', ',');
}

// Nasłuchiwanie zmian
document.addEventListener('DOMContentLoaded', function() {
    // Dodaj nasłuchiwanie dla istniejących elementów
    document.querySelectorAll('.product-select').forEach(select => {
        select.addEventListener('change', function() { updatePrice(this); });
    });
    
    document.querySelectorAll('.quantity-input, .price-input').forEach(input => {
        input.addEventListener('change', function() { 
            calculateItemTotal(this.dataset.index); 
        });
    });
    
    document.getElementById('shippingCost').addEventListener('change', calculateOrderTotal);
    document.getElementById('discountAmount').addEventListener('change', calculateOrderTotal);
});
</script>
@endsection