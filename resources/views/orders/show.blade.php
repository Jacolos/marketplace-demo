@extends('layouts.app')

@section('title', 'Zamówienie ' . $order->order_number)

@section('content')
<div class="container mx-auto px-4">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">{{ $order->order_number }}</h1>
            <p class="text-gray-600">Utworzone: {{ $order->created_at->format('d.m.Y H:i') }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('orders.invoice', $order) }}" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                <i class="fas fa-file-invoice mr-2"></i>Faktura
            </a>
            @if($order->canBeCancelled())
            <a href="{{ route('orders.edit', $order) }}" class="bg-yellow-600 text-white px-4 py-2 rounded hover:bg-yellow-700">
                <i class="fas fa-edit mr-2"></i>Edytuj
            </a>
            <form action="{{ route('orders.destroy', $order) }}" method="POST" class="inline" 
                onsubmit="return confirm('Czy na pewno chcesz anulować to zamówienie?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
                    <i class="fas fa-times mr-2"></i>Anuluj
                </button>
            </form>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Główne informacje -->
        <div class="lg:col-span-2">
            <!-- Status -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-xl font-semibold mb-4">Status zamówienia</h2>
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <span class="text-gray-600">Status:</span>
                        <span class="ml-2 px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full 
                            @if($order->status === 'pending') bg-yellow-100 text-yellow-800
                            @elseif($order->status === 'processing') bg-blue-100 text-blue-800
                            @elseif($order->status === 'shipped') bg-purple-100 text-purple-800
                            @elseif($order->status === 'delivered') bg-green-100 text-green-800
                            @else bg-red-100 text-red-800
                            @endif">
                            {{ ucfirst($order->status) }}
                        </span>
                    </div>
                    @if($order->status !== 'cancelled' && $order->status !== 'delivered')
                    <form action="{{ route('orders.update-status', $order) }}" method="POST" class="flex gap-2">
                        @csrf
                        <select name="status" class="px-3 py-1 border rounded">
                            <option value="pending" {{ $order->status === 'pending' ? 'selected' : '' }}>Oczekujące</option>
                            <option value="processing" {{ $order->status === 'processing' ? 'selected' : '' }}>W realizacji</option>
                            <option value="shipped" {{ $order->status === 'shipped' ? 'selected' : '' }}>Wysłane</option>
                            <option value="delivered" {{ $order->status === 'delivered' ? 'selected' : '' }}>Dostarczone</option>
                        </select>
                        <button type="submit" class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700">
                            Zmień
                        </button>
                    </form>
                    @endif
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <span class="text-gray-600">Płatność:</span>
                        <span class="ml-2 px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full 
                            @if($order->payment_status === 'paid') bg-green-100 text-green-800
                            @else bg-red-100 text-red-800
                            @endif">
                            {{ ucfirst($order->payment_status) }}
                        </span>
                    </div>
                    <div>
                        <span class="text-gray-600">Metoda płatności:</span>
                        <span class="ml-2">{{ ucfirst($order->payment_method ?? 'Nie określono') }}</span>
                    </div>
                </div>
            </div>

            <!-- Pozycje zamówienia -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Pozycje zamówienia</h2>
                <table class="min-w-full">
                    <thead>
                        <tr class="border-b">
                            <th class="text-left py-2">Produkt</th>
                            <th class="text-center py-2">Ilość</th>
                            <th class="text-right py-2">Cena jedn.</th>
                            <th class="text-right py-2">Rabat</th>
                            <th class="text-right py-2">Wartość</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                        <tr class="border-b">
                            <td class="py-3">
                                <div>
                                    <p class="font-medium">{{ $item->product->name }}</p>
                                    <p class="text-sm text-gray-500">SKU: {{ $item->product->sku }}</p>
                                </div>
                            </td>
                            <td class="text-center py-3">{{ $item->quantity }} {{ $item->product->unit }}</td>
                            <td class="text-right py-3">{{ number_format($item->unit_price, 2) }} zł</td>
                            <td class="text-right py-3">{{ $item->discount_percent }}%</td>
                            <td class="text-right py-3 font-medium">{{ number_format($item->total, 2) }} zł</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="border-t">
                            <td colspan="4" class="text-right py-2">Wartość netto:</td>
                            <td class="text-right py-2 font-medium">{{ number_format($order->subtotal, 2) }} zł</td>
                        </tr>
                        <tr>
                            <td colspan="4" class="text-right py-2">VAT (23%):</td>
                            <td class="text-right py-2 font-medium">{{ number_format($order->tax_amount, 2) }} zł</td>
                        </tr>
                        <tr>
                            <td colspan="4" class="text-right py-2">Koszt wysyłki:</td>
                            <td class="text-right py-2 font-medium">{{ number_format($order->shipping_cost, 2) }} zł</td>
                        </tr>
                        @if($order->discount_amount > 0)
                        <tr>
                            <td colspan="4" class="text-right py-2">Rabat:</td>
                            <td class="text-right py-2 font-medium text-red-600">-{{ number_format($order->discount_amount, 2) }} zł</td>
                        </tr>
                        @endif
                        <tr class="border-t text-lg">
                            <td colspan="4" class="text-right py-2 font-semibold">Razem:</td>
                            <td class="text-right py-2 font-bold">{{ number_format($order->total_amount, 2) }} zł</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Panel boczny -->
        <div class="lg:col-span-1">
            <!-- Dane klienta -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-xl font-semibold mb-4">Dane klienta</h2>
                <div class="space-y-2">
                    <p class="font-medium">{{ $order->customer->company_name }}</p>
                    <p class="text-sm text-gray-600">NIP: {{ $order->customer->nip }}</p>
                    <p class="text-sm text-gray-600">{{ $order->customer->contact_person }}</p>
                    <p class="text-sm text-gray-600">{{ $order->customer->email }}</p>
                    <p class="text-sm text-gray-600">{{ $order->customer->phone }}</p>
                </div>
                <a href="{{ route('customers.show', $order->customer) }}" class="mt-4 inline-block text-blue-600 hover:underline">
                    Zobacz profil klienta →
                </a>
            </div>

            <!-- Adres wysyłki -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-xl font-semibold mb-4">Adres wysyłki</h2>
                <div class="text-sm text-gray-600">
                    <p>{{ $order->shipping_address['street'] }}</p>
                    <p>{{ $order->shipping_address['postal_code'] }} {{ $order->shipping_address['city'] }}</p>
                    <p>{{ $order->shipping_address['country'] ?? 'Polska' }}</p>
                </div>
            </div>

            <!-- Adres rozliczeniowy -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-xl font-semibold mb-4">Adres rozliczeniowy</h2>
                <div class="text-sm text-gray-600">
                    <p>{{ $order->billing_address['street'] }}</p>
                    <p>{{ $order->billing_address['postal_code'] }} {{ $order->billing_address['city'] }}</p>
                    <p>{{ $order->billing_address['country'] ?? 'Polska' }}</p>
                </div>
            </div>

            <!-- Uwagi -->
            @if($order->notes)
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Uwagi</h2>
                <p class="text-gray-600">{{ $order->notes }}</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection