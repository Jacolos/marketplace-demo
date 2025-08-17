@extends('layouts.app')

@section('title', 'Edycja produktu')

@section('content')
<div class="container mx-auto px-4">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Edycja produktu: {{ $product->name }}</h1>

    <div class="bg-white rounded-lg shadow p-6">
        <form action="{{ route('products.update', $product) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nazwa produktu *</label>
                    <input type="text" name="name" value="{{ old('name', $product->name) }}" required
                        class="w-full px-3 py-2 border rounded focus:outline-none focus:border-blue-500">
                    @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">SKU</label>
                    <input type="text" value="{{ $product->sku }}" disabled
                        class="w-full px-3 py-2 border rounded bg-gray-100">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Kategoria *</label>
                    <select name="category_id" required
                        class="w-full px-3 py-2 border rounded focus:outline-none focus:border-blue-500">
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Jednostka *</label>
                    <select name="unit" required
                        class="w-full px-3 py-2 border rounded focus:outline-none focus:border-blue-500">
                        <option value="szt" {{ old('unit', $product->unit) == 'szt' ? 'selected' : '' }}>sztuka</option>
                        <option value="kg" {{ old('unit', $product->unit) == 'kg' ? 'selected' : '' }}>kilogram</option>
                        <option value="opak" {{ old('unit', $product->unit) == 'opak' ? 'selected' : '' }}>opakowanie</option>
                        <option value="m" {{ old('unit', $product->unit) == 'm' ? 'selected' : '' }}>metr</option>
                        <option value="l" {{ old('unit', $product->unit) == 'l' ? 'selected' : '' }}>litr</option>
                    </select>
                    @error('unit') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Cena detaliczna *</label>
                    <input type="number" name="price" value="{{ old('price', $product->price) }}" step="0.01" min="0" required
                        class="w-full px-3 py-2 border rounded focus:outline-none focus:border-blue-500">
                    @error('price') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Cena hurtowa</label>
                    <input type="number" name="wholesale_price" value="{{ old('wholesale_price', $product->wholesale_price) }}" step="0.01" min="0"
                        class="w-full px-3 py-2 border rounded focus:outline-none focus:border-blue-500">
                    @error('wholesale_price') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Stan magazynowy *</label>
                    <input type="number" name="stock_quantity" value="{{ old('stock_quantity', $product->stock_quantity) }}" min="0" required
                        class="w-full px-3 py-2 border rounded focus:outline-none focus:border-blue-500">
                    @error('stock_quantity') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Min. zamówienie *</label>
                    <input type="number" name="min_order_quantity" value="{{ old('min_order_quantity', $product->min_order_quantity) }}" min="1" required
                        class="w-full px-3 py-2 border rounded focus:outline-none focus:border-blue-500">
                    @error('min_order_quantity') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Opis</label>
                    <textarea name="description" rows="4"
                        class="w-full px-3 py-2 border rounded focus:outline-none focus:border-blue-500">{{ old('description', $product->description) }}</textarea>
                    @error('description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                @if($product->images && count($product->images) > 0)
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Obecne zdjęcia</label>
                    <div class="grid grid-cols-4 gap-2">
                        @foreach($product->images as $image)
                            <img src="{{ $image }}" alt="Product image" class="rounded object-cover w-full h-24">
                        @endforeach
                    </div>
                </div>
                @endif

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Dodaj nowe zdjęcia</label>
                    <input type="file" name="images[]" multiple accept="image/*"
                        class="w-full px-3 py-2 border rounded focus:outline-none focus:border-blue-500">
                    <p class="text-sm text-gray-500 mt-1">Nowe zdjęcia zostaną dodane do istniejących.</p>
                    @error('images.*') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="flex items-center">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $product->is_active) ? 'checked' : '' }} class="mr-2">
                        <span>Produkt aktywny</span>
                    </label>
                </div>

                <div>
                    <label class="flex items-center">
                        <input type="checkbox" name="is_featured" value="1" {{ old('is_featured', $product->is_featured) ? 'checked' : '' }} class="mr-2">
                        <span>Produkt polecany</span>
                    </label>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-4">
                <a href="{{ route('products.show', $product) }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400">
                    Anuluj
                </a>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    <i class="fas fa-save mr-2"></i>Zapisz zmiany
                </button>
            </div>
        </form>
    </div>
</div>
@endsection