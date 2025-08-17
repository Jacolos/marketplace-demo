@extends('layouts.app')

@section('title', $product->name)

@section('content')
<div class="container mx-auto px-4">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">{{ $product->name }}</h1>
            <p class="text-gray-600">SKU: {{ $product->sku }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('products.edit', $product) }}" class="bg-yellow-600 text-white px-4 py-2 rounded hover:bg-yellow-700">
                <i class="fas fa-edit mr-2"></i>Edytuj
            </a>
            @if(!$product->orderItems()->exists())
            <form action="{{ route('products.destroy', $product) }}" method="POST" class="inline" 
                onsubmit="return confirm('Czy na pewno chcesz usunąć ten produkt?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
                    <i class="fas fa-trash mr-2"></i>Usuń
                </button>
            </form>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Główne informacje -->
        <div class="lg:col-span-2">
            <!-- Zdjęcia -->
            @if($product->images && count($product->images) > 0)
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-xl font-semibold mb-4">Zdjęcia</h2>
                <div class="grid grid-cols-3 gap-4">
                    @foreach($product->images as $image)
                        <img src="{{ $image }}" alt="{{ $product->name }}" class="rounded object-cover w-full h-32">
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Opis -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-xl font-semibold mb-4">Opis</h2>
                <p class="text-gray-600">{{ $product->description ?: 'Brak opisu' }}</p>
            </div>

            <!-- Statystyki -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Statystyki sprzedaży</h2>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <p class="text-gray-500 text-sm">Sprzedano</p>
                        <p class="text-2xl font-bold">{{ $stats['total_sold'] }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">Przychód</p>
                        <p class="text-2xl font-bold">{{ number_format($stats['total_revenue'], 2) }} zł</p>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">Zamówień</p>
                        <p class="text-2xl font-bold">{{ $stats['orders_count'] }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">Śr. ilość</p>
                        <p class="text-2xl font-bold">{{ number_format($stats['avg_order_quantity'], 1) }}</p>
                    </div>
                </div>

                @if($salesHistory->count() > 0)
                <div class="mt-6">
                    <h3 class="font-semibold mb-2">Historia sprzedaży (30 dni)</h3>
                    <canvas id="salesChart" height="100"></canvas>
                </div>
                @endif
            </div>
        </div>

        <!-- Panel boczny -->
        <div class="lg:col-span-1">
            <!-- Informacje podstawowe -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-xl font-semibold mb-4">Informacje</h2>
                <dl class="space-y-2">
                    <div>
                        <dt class="text-gray-500 text-sm">Kategoria</dt>
                        <dd class="font-medium">{{ $product->category->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 text-sm">Cena detaliczna</dt>
                        <dd class="font-medium text-lg">{{ number_format($product->price, 2) }} zł</dd>
                    </div>
                    @if($product->wholesale_price)
                    <div>
                        <dt class="text-gray-500 text-sm">Cena hurtowa</dt>
                        <dd class="font-medium">{{ number_format($product->wholesale_price, 2) }} zł</dd>
                    </div>
                    @endif
                    <div>
                        <dt class="text-gray-500 text-sm">Stan magazynowy</dt>
                        <dd class="font-medium {{ $product->stock_quantity > 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ $product->stock_quantity }} {{ $product->unit }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 text-sm">Min. zamówienie</dt>
                        <dd class="font-medium">{{ $product->min_order_quantity }} {{ $product->unit }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 text-sm">Status</dt>
                        <dd>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                {{ $product->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $product->is_active ? 'Aktywny' : 'Nieaktywny' }}
                            </span>
                        </dd>
                    </div>
                    @if($product->is_featured)
                    <div>
                        <dt class="text-gray-500 text-sm">Wyróżnienie</dt>
                        <dd><span class="text-yellow-600"><i class="fas fa-star"></i> Produkt polecany</span></dd>
                    </div>
                    @endif
                </dl>
            </div>

            <!-- Atrybuty -->
            @if($product->attributes && count($product->attributes) > 0)
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Atrybuty</h2>
                <dl class="space-y-2">
                    @foreach($product->attributes as $key => $value)
                    <div>
                        <dt class="text-gray-500 text-sm">{{ ucfirst($key) }}</dt>
                        <dd class="font-medium">{{ $value }}</dd>
                    </div>
                    @endforeach
                </dl>
            </div>
            @endif
        </div>
    </div>
</div>

@if($salesHistory->count() > 0)
<script>
    const ctx = document.getElementById('salesChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: {!! json_encode($salesHistory->pluck('date')) !!},
            datasets: [{
                label: 'Ilość sprzedana',
                data: {!! json_encode($salesHistory->pluck('quantity')) !!},
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
</script>
@endif
@endsection