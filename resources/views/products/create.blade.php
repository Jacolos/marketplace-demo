@extends('layouts.app')

@section('title', 'Nowy produkt')

@section('content')
<div class="container mx-auto px-4">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Nowy produkt</h1>

    <div class="bg-white rounded-lg shadow p-6">
        <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nazwa produktu *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                        class="w-full px-3 py-2 border rounded focus:outline-none focus:border-blue-500">
                    @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">SKU</label>
                    <input type="text" name="sku" value="{{ old('sku') }}"
                        placeholder="Zostanie wygenerowany automatycznie"
                        class="w-full px-3 py-2 border rounded focus:outline-none focus:border-blue-500">
                    @error('sku') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Kategoria *</label>
                    <select name="category_id" required
                        class="w-full px-3 py-2 border rounded focus:outline-none focus:border-blue-500">
                        <option value="">Wybierz kategorię</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
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
                        <option value="szt" {{ old('unit') == 'szt' ? 'selected' : '' }}>sztuka</option>
                        <option value="kg" {{ old('unit') == 'kg' ? 'selected' : '' }}>kilogram</option>
                        <option value="opak" {{ old('unit') == 'opak' ? 'selected' : '' }}>opakowanie</option>
                        <option value="m" {{ old('unit') == 'm' ? 'selected' : '' }}>metr</option>
                        <option value="l" {{ old('unit') == 'l' ? 'selected' : '' }}>litr</option>
                    </select>
                    @error('unit') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Cena detaliczna *</label>
                    <input type="number" name="price" value="{{ old('price') }}" step="0.01" min="0" required
                        class="w-full px-3 py-2 border rounded focus:outline-none focus:border-blue-500">
                    @error('price') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Cena hurtowa</label>
                    <input type="number" name="wholesale_price" value="{{ old('wholesale_price') }}" step="0.01" min="0"
                        class="w-full px-3 py-2 border rounded focus:outline-none focus:border-blue-500">
                    @error('wholesale_price') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Stan magazynowy *</label>
                    <input type="number" name="stock_quantity" value="{{ old('stock_quantity', 0) }}" min="0" required
                        class="w-full px-3 py-2 border rounded focus:outline-none focus:border-blue-500">
                    @error('stock_quantity') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Min. zamówienie *</label>
                    <input type="number" name="min_order_quantity" value="{{ old('min_order_quantity', 1) }}" min="1" required
                        class="w-full px-3 py-2 border rounded focus:outline-none focus:border-blue-500">
                    @error('min_order_quantity') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Opis</label>
                    <textarea name="description" rows="4"
                        class="w-full px-3 py-2 border rounded focus:outline-none focus:border-blue-500">{{ old('description') }}</textarea>
                    @error('description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Zdjęcia</label>
                    <input type="file" name="images[]" multiple accept="image/*"
                        class="w-full px-3 py-2 border rounded focus:outline-none focus:border-blue-500">
                    <p class="text-sm text-gray-500 mt-1">Możesz wybrać wiele zdjęć. Max 2MB na zdjęcie.</p>
                    @error('images.*') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="flex items-center">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="mr-2">
                        <span>Produkt aktywny</span>
                    </label>
                </div>

                <div>
                    <label class="flex items-center">
                        <input type="checkbox" name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }} class="mr-2">
                        <span>Produkt polecany</span>
                    </label>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-4">
                <a href="{{ route('products.index') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400">
                    Anuluj
                </a>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    <i class="fas fa-save mr-2"></i>Zapisz produkt
                </button>
            </div>
        </form>
    </div>
</div>
@endsection