@extends('layouts.app')

@section('title', 'Produkty')

@section('content')
<div class="container mx-auto px-4">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Produkty</h1>
        <div class="flex gap-2">
            <a href="{{ route('products.export') }}" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                <i class="fas fa-download mr-2"></i>Export CSV
            </a>
            <a href="{{ route('products.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                <i class="fas fa-plus mr-2"></i>Nowy produkt
            </a>
        </div>
    </div>

    <!-- Filtry -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <form method="GET" action="{{ route('products.index') }}" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" 
                    placeholder="Szukaj po nazwie lub SKU..."
                    class="w-full px-3 py-2 border rounded focus:outline-none focus:border-blue-500">
            </div>
            
            <select name="category_id" class="px-3 py-2 border rounded focus:outline-none focus:border-blue-500">
                <option value="">Wszystkie kategorie</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
            
            <label class="flex items-center">
                <input type="checkbox" name="in_stock" value="1" {{ request('in_stock') ? 'checked' : '' }} class="mr-2">
                <span>Tylko dostępne</span>
            </label>
            
            <label class="flex items-center">
                <input type="checkbox" name="featured" value="1" {{ request('featured') ? 'checked' : '' }} class="mr-2">
                <span>Tylko polecane</span>
            </label>
            
            <button type="submit" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">
                <i class="fas fa-search mr-2"></i>Szukaj
            </button>
        </form>
    </div>

    <!-- Tabela produktów -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nazwa</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategoria</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cena</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stan</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Akcje</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($products as $product)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">{{ $product->sku }}</td>
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            @if($product->images && count($product->images) > 0)
                                <img src="{{ $product->images[0] }}" alt="{{ $product->name }}" 
                                    class="h-10 w-10 rounded object-cover mr-3">
                            @endif
                            <div>
                                <div class="text-sm font-medium text-gray-900">{{ $product->name }}</div>
                                @if($product->is_featured)
                                    <span class="text-xs text-yellow-600"><i class="fas fa-star"></i> Polecany</span>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $product->category->name }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ number_format($product->price, 2) }} zł</div>
                        @if($product->wholesale_price)
                            <div class="text-xs text-gray-500">Hurt: {{ number_format($product->wholesale_price, 2) }} zł</div>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="text-sm {{ $product->stock_quantity > 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ $product->stock_quantity }} {{ $product->unit }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            {{ $product->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $product->is_active ? 'Aktywny' : 'Nieaktywny' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <a href="{{ route('products.show', $product) }}" class="text-blue-600 hover:text-blue-900 mr-3">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="{{ route('products.edit', $product) }}" class="text-yellow-600 hover:text-yellow-900 mr-3">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('products.toggle-featured', $product) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="text-purple-600 hover:text-purple-900">
                                <i class="fas fa-star"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                        Brak produktów do wyświetlenia
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Paginacja -->
    <div class="mt-4">
        {{ $products->links() }}
    </div>
</div>
@endsection