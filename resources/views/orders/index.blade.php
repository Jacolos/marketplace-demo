@extends('layouts.app')

@section('title', 'Zamówienia')

@section('content')
<div class="container mx-auto px-4">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Zamówienia</h1>
        <a href="{{ route('orders.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            <i class="fas fa-plus mr-2"></i>Nowe zamówienie
        </a>
    </div>

    <!-- Filtry -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <form method="GET" action="{{ route('orders.index') }}" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" 
                    placeholder="Szukaj po numerze lub kliencie..."
                    class="w-full px-3 py-2 border rounded focus:outline-none focus:border-blue-500">
            </div>
            
            <select name="status" class="px-3 py-2 border rounded focus:outline-none focus:border-blue-500">
                <option value="">Wszystkie statusy</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Oczekujące</option>
                <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>W realizacji</option>
                <option value="shipped" {{ request('status') == 'shipped' ? 'selected' : '' }}>Wysłane</option>
                <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Dostarczone</option>
                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Anulowane</option>
            </select>
            
            <input type="date" name="date_from" value="{{ request('date_from') }}" 
                class="px-3 py-2 border rounded focus:outline-none focus:border-blue-500">
            
            <input type="date" name="date_to" value="{{ request('date_to') }}" 
                class="px-3 py-2 border rounded focus:outline-none focus:border-blue-500">
            
            <button type="submit" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">
                <i class="fas fa-search mr-2"></i>Szukaj
            </button>
            
            <a href="{{ route('orders.index') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400">
                <i class="fas fa-times mr-2"></i>Wyczyść
            </a>
        </form>
    </div>

    <!-- Tabela zamówień -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Nr zamówienia
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Klient
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Status
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Płatność
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Wartość
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Data
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Akcje
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($orders as $order)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <a href="{{ route('orders.show', $order) }}" class="text-blue-600 hover:underline font-medium">
                            {{ $order->order_number }}
                        </a>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div>
                            <div class="text-sm font-medium text-gray-900">{{ $order->customer->company_name }}</div>
                            <div class="text-sm text-gray-500">{{ $order->customer->email }}</div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            @if($order->status === 'pending') bg-yellow-100 text-yellow-800
                            @elseif($order->status === 'processing') bg-blue-100 text-blue-800
                            @elseif($order->status === 'shipped') bg-purple-100 text-purple-800
                            @elseif($order->status === 'delivered') bg-green-100 text-green-800
                            @else bg-red-100 text-red-800
                            @endif">
                            {{ ucfirst($order->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            @if($order->payment_status === 'paid') bg-green-100 text-green-800
                            @elseif($order->payment_status === 'unpaid') bg-red-100 text-red-800
                            @else bg-yellow-100 text-yellow-800
                            @endif">
                            {{ ucfirst($order->payment_status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ number_format($order->total_amount, 2) }} zł
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $order->created_at->format('d.m.Y H:i') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <a href="{{ route('orders.show', $order) }}" class="text-blue-600 hover:text-blue-900 mr-3">
                            <i class="fas fa-eye"></i>
                        </a>
                        @if($order->canBeCancelled())
                        <a href="{{ route('orders.edit', $order) }}" class="text-yellow-600 hover:text-yellow-900 mr-3">
                            <i class="fas fa-edit"></i>
                        </a>
                        @endif
                        <a href="{{ route('orders.invoice', $order) }}" class="text-green-600 hover:text-green-900">
                            <i class="fas fa-file-invoice"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                        Brak zamówień do wyświetlenia
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Paginacja -->
    <div class="mt-4">
        {{ $orders->links() }}
    </div>
</div>
@endsection