@extends('layouts.app')

@section('title', $customer->company_name)

@section('content')
<div class="container mx-auto px-4">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">{{ $customer->company_name }}</h1>
            <p class="text-gray-600">NIP: {{ $customer->nip }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('customers.orders', $customer) }}" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                <i class="fas fa-clipboard-list mr-2"></i>Zamówienia
            </a>
            <a href="{{ route('customers.edit', $customer) }}" class="bg-yellow-600 text-white px-4 py-2 rounded hover:bg-yellow-700">
                <i class="fas fa-edit mr-2"></i>Edytuj
            </a>
            @if(!$customer->orders()->exists())
            <form action="{{ route('customers.destroy', $customer) }}" method="POST" class="inline" 
                onsubmit="return confirm('Czy na pewno chcesz usunąć tego klienta?')">
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
            <!-- Statystyki -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-xl font-semibold mb-4">Statystyki</h2>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    <div>
                        <p class="text-gray-500 text-sm">Wszystkie zamówienia</p>
                        <p class="text-2xl font-bold">{{ $stats['total_orders'] }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">Oczekujące</p>
                        <p class="text-2xl font-bold text-yellow-600">{{ $stats['pending_orders'] }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">Wydano łącznie</p>
                        <p class="text-2xl font-bold">{{ number_format($stats['total_spent'], 2) }} zł</p>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">Śr. wartość zamówienia</p>
                        <p class="text-2xl font-bold">{{ number_format($stats['average_order_value'], 2) }} zł</p>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">Dostępny kredyt</p>
                        <p class="text-2xl font-bold {{ $stats['available_credit'] > 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ number_format($stats['available_credit'], 2) }} zł
                        </p>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">Ostatnie zamówienie</p>
                        <p class="text-lg font-medium">
                            {{ $stats['last_order_date'] ? $stats['last_order_date']->format('d.m.Y') : 'Brak' }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Ostatnie zamówienia -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-xl font-semibold mb-4">Ostatnie zamówienia</h2>
                <table class="min-w-full">
                    <thead>
                        <tr class="border-b">
                            <th class="text-left py-2">Nr zamówienia</th>
                            <th class="text-left py-2">Status</th>
                            <th class="text-right py-2">Wartość</th>
                            <th class="text-left py-2">Data</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentOrders as $order)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-2">
                                <a href="{{ route('orders.show', $order) }}" class="text-blue-600 hover:underline">
                                    {{ $order->order_number }}
                                </a>
                            </td>
                            <td class="py-2">
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
                            <td class="py-2 text-right">{{ number_format($order->total_amount, 2) }} zł</td>
                            <td class="py-2">{{ $order->created_at->format('d.m.Y') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="py-4 text-center text-gray-500">Brak zamówień</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                @if($recentOrders->count() > 0)
                <div class="mt-4">
                    <a href="{{ route('customers.orders', $customer) }}" class="text-blue-600 hover:underline">
                        Zobacz wszystkie zamówienia →
                    </a>
                </div>
                @endif
            </div>

            <!-- Aktywność API -->
            @if($apiActivity->count() > 0)
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Ostatnia aktywność API</h2>
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b">
                            <th class="text-left py-2">Endpoint</th>
                            <th class="text-left py-2">Metoda</th>
                            <th class="text-left py-2">Status</th>
                            <th class="text-left py-2">Data</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($apiActivity as $log)
                        <tr class="border-b">
                            <td class="py-2">{{ $log->endpoint }}</td>
                            <td class="py-2">
                                <span class="px-2 py-1 text-xs rounded
                                    @if($log->method === 'GET') bg-blue-100 text-blue-800
                                    @elseif($log->method === 'POST') bg-green-100 text-green-800
                                    @elseif($log->method === 'DELETE') bg-red-100 text-red-800
                                    @else bg-yellow-100 text-yellow-800
                                    @endif">
                                    {{ $log->method }}
                                </span>
                            </td>
                            <td class="py-2">
                                <span class="text-xs {{ $log->response_code < 400 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $log->response_code }}
                                </span>
                            </td>
                            <td class="py-2">{{ $log->created_at->format('d.m H:i') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>

        <!-- Panel boczny -->
        <div class="lg:col-span-1">
            <!-- Dane kontaktowe -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-xl font-semibold mb-4">Dane kontaktowe</h2>
                <dl class="space-y-2">
                    <div>
                        <dt class="text-gray-500 text-sm">Osoba kontaktowa</dt>
                        <dd class="font-medium">{{ $customer->contact_person }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 text-sm">Email</dt>
                        <dd class="font-medium">{{ $customer->email }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 text-sm">Telefon</dt>
                        <dd class="font-medium">{{ $customer->phone }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 text-sm">Adres</dt>
                        <dd class="font-medium">
                            {{ $customer->address }}<br>
                            {{ $customer->postal_code }} {{ $customer->city }}<br>
                            {{ $customer->country }}
                        </dd>
                    </div>
                </dl>
            </div>

            <!-- Informacje handlowe -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-xl font-semibold mb-4">Informacje handlowe</h2>
                <dl class="space-y-2">
                    <div>
                        <dt class="text-gray-500 text-sm">Typ klienta</dt>
                        <dd>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                @if($customer->customer_type === 'vip') bg-purple-100 text-purple-800
                                @elseif($customer->customer_type === 'wholesale') bg-blue-100 text-blue-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ ucfirst($customer->customer_type) }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 text-sm">Rabat</dt>
                        <dd class="font-medium text-green-600">{{ $customer->discount_percent }}%</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 text-sm">Limit kredytowy</dt>
                        <dd class="font-medium">{{ number_format($customer->credit_limit, 2) }} zł</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 text-sm">Obecne saldo</dt>
                        <dd class="font-medium {{ $customer->current_balance > 0 ? 'text-red-600' : 'text-green-600' }}">
                            {{ number_format($customer->current_balance, 2) }} zł
                        </dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 text-sm">Status</dt>
                        <dd>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                {{ $customer->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $customer->is_active ? 'Aktywny' : 'Nieaktywny' }}
                            </span>
                        </dd>
                    </div>
                </dl>

                @if($customer->current_balance > 0)
                <form action="{{ route('customers.clearBalance', $customer) }}" method="POST" class="mt-4">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="w-full bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700"
                        onclick="return confirm('Czy na pewno chcesz wyczyścić saldo klienta?')">
                        Wyczyść saldo
                    </button>
                </form>
                @endif
            </div>

            <!-- Klucz API -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Integracja API</h2>
                <div class="mb-4">
                    <label class="text-gray-500 text-sm">Klucz API</label>
                    <div class="mt-1 flex rounded-md shadow-sm">
                        <input type="password" value="{{ $customer->api_key }}" disabled
                            class="flex-1 rounded-l-md border-gray-300 bg-gray-100 px-3 py-2">
                        <button onclick="this.previousElementSibling.type = this.previousElementSibling.type === 'password' ? 'text' : 'password'"
                            class="inline-flex items-center px-3 rounded-r-md border border-l-0 border-gray-300 bg-gray-50 text-gray-500 hover:bg-gray-100">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                <form action="{{ route('customers.regenerate-api-key', $customer) }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
                        onclick="return confirm('Czy na pewno chcesz wygenerować nowy klucz API?')">
                        <i class="fas fa-sync mr-2"></i>Wygeneruj nowy klucz
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection