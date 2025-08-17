<div>
    <!-- Search and filters -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Search input -->
            <div class="md:col-span-2">
                <input type="text" 
                    wire:model.debounce.300ms="search"
                    placeholder="Szukaj produktów..."
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
            </div>
            
            <!-- Category filter -->
            <div>
                <select wire:model="categoryId" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                    <option value="">Wszystkie kategorie</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            
            <!-- Filters -->
            <div class="flex items-center space-x-4">
                <label class="flex items-center">
                    <input type="checkbox" wire:model="inStock" class="mr-2">
                    <span class="text-sm">Dostępne</span>
                </label>
                <label class="flex items-center">
                    <input type="checkbox" wire:model="featured" class="mr-2">
                    <span class="text-sm">Polecane</span>
                </label>
            </div>
        </div>
    </div>

    <!-- Products grid - Responsive -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @foreach($products as $product)
        <div class="bg-white rounded-lg shadow hover:shadow-lg transition-shadow">
            @if($product->images && count($product->images) > 0)
                <img src="{{ $product->images[0] }}" alt="{{ $product->name }}" 
                    class="w-full h-48 object-cover rounded-t-lg">
            @else
                <div class="w-full h-48 bg-gray-200 rounded-t-lg flex items-center justify-center">
                    <i class="fas fa-image text-gray-400 text-4xl"></i>
                </div>
            @endif
            
            <div class="p-4">
                <h3 class="font-semibold text-lg mb-2">{{ $product->name }}</h3>
                <p class="text-gray-600 text-sm mb-2">{{ Str::limit($product->description, 50) }}</p>
                <p class="text-gray-500 text-xs mb-3">SKU: {{ $product->sku }}</p>
                
                <div class="flex justify-between items-center mb-3">
                    <span class="text-2xl font-bold text-blue-600">{{ number_format($product->price, 2) }} zł</span>
                    <span class="text-sm text-gray-500">{{ $product->unit }}</span>
                </div>
                
                <div class="flex justify-between items-center">
                    <span class="text-sm {{ $product->stock_quantity > 0 ? 'text-green-600' : 'text-red-600' }}">
                        @if($product->stock_quantity > 0)
                            <i class="fas fa-check-circle"></i> {{ $product->stock_quantity }} szt
                        @else
                            <i class="fas fa-times-circle"></i> Brak
                        @endif
                    </span>
                    
                    <button wire:click="addToCart({{ $product->id }})" 
                        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors disabled:opacity-50"
                        {{ $product->stock_quantity < 1 ? 'disabled' : '' }}>
                        <i class="fas fa-cart-plus"></i>
                    </button>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $products->links() }}
    </div>
</div>