<div>
    <div class="bg-white rounded-lg shadow p-6">
        <!-- Błędy ogólne -->
        @if($errors->has('general'))
            <div class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4">
                <p class="font-bold">Błąd!</p>
                <p>{{ $errors->first('general') }}</p>
            </div>
        @endif

        <form wire:submit.prevent="saveOrder">
            <!-- Wybór klienta -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Klient *</label>
                <select wire:model.live="customer_id" 
                    class="w-full px-3 py-2 border rounded focus:outline-none focus:border-blue-500">
                    <option value="">-- Wybierz klienta --</option>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}">
                            {{ $customer->company_name }} (NIP: {{ $customer->nip }}, Rabat: {{ $customer->discount_percent }}%)
                        </option>
                    @endforeach
                </select>
                @error('customer_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <!-- Pozycje zamówienia -->
            <div class="mb-6">
                <h3 class="text-lg font-semibold mb-3">Pozycje zamówienia</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="border-b bg-gray-50">
                                <th class="text-left py-2 px-2">Produkt</th>
                                <th class="text-center py-2 px-2">Ilość</th>
                                <th class="text-right py-2 px-2">Cena jedn.</th>
                                <th class="text-right py-2 px-2">Wartość netto</th>
                                <th class="py-2 px-2"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $index => $item)
                            <tr class="border-b" wire:key="item-{{ $index }}">
                                <td class="py-2 pr-2">
                                    <select 
                                        wire:model.live="items.{{ $index }}.product_id"
                                        class="w-full px-2 py-1 border rounded focus:outline-none focus:border-blue-500">
                                        <option value="">-- Wybierz produkt --</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}">
                                                {{ $product->name }} ({{ $product->sku }}) 
                                                - Stan: {{ $product->stock_quantity }} {{ $product->unit }}
                                                - Cena bazowa: {{ number_format($product->price, 2) }} zł
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('items.'.$index.'.product_id') 
                                        <span class="text-red-500 text-xs">Wybierz produkt</span> 
                                    @enderror
                                </td>
                                <td class="text-center py-2 px-2">
                                    <input type="number" 
                                        wire:model.live.debounce.500ms="items.{{ $index }}.quantity"
                                        min="1" 
                                        class="w-20 px-2 py-1 border rounded text-center focus:outline-none focus:border-blue-500">
                                    @error('items.'.$index.'.quantity') 
                                        <span class="text-red-500 text-xs block">Min. 1</span> 
                                    @enderror
                                </td>
                                <td class="text-right py-2 px-2">
                                    @if($customer_id && !empty($item['unit_price']))
                                        <span class="text-green-600 font-semibold">
                                            {{ number_format($item['unit_price'], 2) }} zł
                                        </span>
                                        @if(isset($item['product_id']) && $item['product_id'])
                                            @php
                                                $originalProduct = $products->firstWhere('id', $item['product_id']);
                                                $originalPrice = $originalProduct ? $originalProduct->price : 0;
                                            @endphp
                                            @if($originalPrice > $item['unit_price'])
                                                <br>
                                                <span class="text-xs text-gray-500 line-through">
                                                    {{ number_format($originalPrice, 2) }} zł
                                                </span>
                                            @endif
                                        @endif
                                    @elseif(!$customer_id)
                                        <span class="text-gray-400 text-xs">Wybierz klienta</span>
                                    @else
                                        0,00 zł
                                    @endif
                                    @error('items.'.$index.'.unit_price') 
                                        <span class="text-red-500 text-xs block">{{ $message }}</span> 
                                    @enderror
                                </td>
                                <td class="text-right py-2 px-2 font-semibold">
                                    @if(!empty($item['subtotal']))
                                        {{ number_format($item['subtotal'], 2) }} zł
                                    @else
                                        0,00 zł
                                    @endif
                                </td>
                                <td class="text-center py-2 pl-2">
                                    <button type="button" wire:click="removeItem({{ $index }})" 
                                        class="text-red-600 hover:text-red-800">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <button type="button" wire:click="addEmptyItem" class="mt-3 text-blue-600 hover:text-blue-800">
                    <i class="fas fa-plus mr-1"></i> Dodaj pozycję
                </button>
                
                @error('items') 
                    <span class="text-red-500 text-sm block mt-2">{{ $message }}</span> 
                @enderror
                
                @if(!$customer_id)
                    <div class="mt-3 p-3 bg-yellow-50 border-l-4 border-yellow-400 text-yellow-700">
                        <p class="text-sm">
                            <i class="fas fa-info-circle mr-1"></i>
                            Wybierz najpierw klienta, aby zobaczyć ceny z uwzględnieniem rabatów.
                        </p>
                    </div>
                @endif
            </div>

            <!-- Adres wysyłki -->
            <div class="mb-6">
                <h3 class="text-lg font-semibold mb-3">Adres wysyłki</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Ulica i numer *</label>
                        <input type="text" wire:model.defer="shippingAddress.street" 
                            class="w-full px-3 py-2 border rounded focus:outline-none focus:border-blue-500">
                        @error('shippingAddress.street') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Miasto *</label>
                        <input type="text" wire:model.defer="shippingAddress.city" 
                            class="w-full px-3 py-2 border rounded focus:outline-none focus:border-blue-500">
                        @error('shippingAddress.city') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kod pocztowy *</label>
                        <input type="text" wire:model.defer="shippingAddress.postal_code" 
                            placeholder="00-000"
                            class="w-full px-3 py-2 border rounded focus:outline-none focus:border-blue-500">
                        @error('shippingAddress.postal_code') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kraj</label>
                        <input type="text" wire:model.defer="shippingAddress.country" 
                            class="w-full px-3 py-2 border rounded focus:outline-none focus:border-blue-500">
                    </div>
                </div>
            </div>

            <!-- Metoda płatności i uwagi -->
            <div class="mb-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Metoda płatności</label>
                    <select wire:model.defer="paymentMethod" 
                        class="w-full px-3 py-2 border rounded focus:outline-none focus:border-blue-500">
                        <option value="transfer">Przelew bankowy</option>
                        <option value="card">Karta płatnicza</option>
                        <option value="cash">Gotówka</option>
                        <option value="deferred">Płatność odroczona</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Uwagi do zamówienia</label>
                    <textarea wire:model.defer="notes" rows="1" 
                        placeholder="Opcjonalne uwagi..."
                        class="w-full px-3 py-2 border rounded focus:outline-none focus:border-blue-500"></textarea>
                </div>
            </div>

            <!-- Podsumowanie -->
            <div class="bg-gray-50 p-4 rounded mb-6">
                <div class="text-right">
                    @if($orderTotal > 0)
                        <div class="mb-2">
                            <span class="text-gray-600">Wartość netto: </span>
                            <span class="font-semibold">{{ number_format($orderTotal / 1.23, 2) }} zł</span>
                        </div>
                        <div class="mb-2">
                            <span class="text-gray-600">VAT (23%): </span>
                            <span class="font-semibold">{{ number_format($orderTotal - ($orderTotal / 1.23), 2) }} zł</span>
                        </div>
                    @endif
                    <div class="text-lg">
                        <span class="font-semibold">Razem do zapłaty: </span>
                        <span class="text-2xl font-bold text-blue-600">{{ number_format($orderTotal, 2) }} zł</span>
                    </div>
                </div>
            </div>

            <!-- Przyciski -->
            <div class="flex justify-end gap-4">
                <a href="{{ route('orders.index') }}" 
                    class="bg-gray-300 text-gray-700 px-6 py-2 rounded hover:bg-gray-400 transition">
                    Anuluj
                </a>
                <button type="submit" 
                    class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition
                    @if($orderTotal <= 0) opacity-50 cursor-not-allowed @endif"
                    @if($orderTotal <= 0) disabled @endif>
                    <i class="fas fa-save mr-2"></i>Utwórz zamówienie
                </button>
            </div>
        </form>
    </div>

    <!-- Loading indicator -->
    <div wire:loading.flex wire:target="saveOrder,customer_id,items" 
        class="fixed inset-0 bg-black bg-opacity-50 items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg shadow-xl">
            <div class="flex items-center">
                <svg class="animate-spin h-8 w-8 mr-3 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-lg">Przetwarzanie...</span>
            </div>
        </div>
    </div>
</div>