@extends('layouts.app')

@section('title', 'Klienci')

@section('content')
<div class="container mx-auto px-4">
    <!-- Statystyki -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-gray-500 text-sm">Wszyscy klienci</p>
            <p class="text-2xl font-bold">{{ $stats['total_customers'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-gray-500 text-sm">Aktywni</p>
            <p class="text-2xl font-bold text-green-600">{{ $stats['active_customers'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-gray-500 text-sm">VIP</p>
            <p class="text-2xl font-bold text-purple-600">{{ $stats['vip_customers'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-gray-500 text-sm">Zadłużenie</p>
            <p class="text-2xl font-bold text-red-600">{{ number_format($stats['total_debt'], 2) }} zł</p>
        </div>
    </div>

    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Klienci</h1>
        <a href="{{ route('customers.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            <i class="fas fa-plus mr-2"></i>Nowy klient
        </a>
    </div>

    <!-- Filtry -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <form method="GET" action="{{ route('customers.index') }}" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" 
                    placeholder="Szukaj po nazwie, NIP lub email..."
                    class="w-full px-3 py-2 border rounded focus:outline-none focus:border-blue-500">
            </div>
            
            <select name="customer_type" class="px-3 py-2 border rounded focus:outline-none focus:border-blue-500">
                <option value="">Wszystkie typy</option>
                <option value="retail" {{ request('customer_type') == 'retail' ? 'selected' : '' }}>Detaliczny</option>
                <option value="wholesale" {{ request('customer_type') == 'wholesale' ? 'selected' : '' }}>Hurtowy</option>
                <option value="vip" {{ request('customer_type') == 'vip' ? 'selected' : '' }}>VIP</option>
            </select>
            
            <select name="is_active" class="px-3 py-2 border rounded focus:outline-none focus:border-blue-500">
                <option value="">Wszyscy</option>
                <option value="1" {{ request('is_active') == '1' ? 'selected' : '' }}>Aktywni</option>
                <option value="0" {{ request('is_active') == '0' ? 'selected' : '' }}>Nieaktywni</option>
            </select>
            
            <button type="submit" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">
                <i class="fas fa-search mr-2"></i>Szukaj
            </button>
        </form>
    </div>

    <!-- Tabela klientów -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Firma</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kontakt</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Typ</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Limit/Saldo</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Akcje</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($customers as $customer)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <div>
                            <div class="text-sm font-medium text-gray-900">{{ $customer->company_name }}</div>
                            <div class="text-sm text-gray-500">NIP: {{ $customer->nip }}</div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div>
                            <div class="text-sm text-gray-900">{{ $customer->contact_person }}</div>
                            <div class="text-sm text-gray-500">{{ $customer->email }}</div>
                            <div class="text-sm text-gray-500">{{ $customer->phone }}</div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            @if($customer->customer_type === 'vip') bg-purple-100 text-purple-800
                            @elseif($customer->customer_type === 'wholesale') bg-blue-100 text-blue-800
                            @else bg-gray-100 text-gray-800
                            @endif">
                            {{ ucfirst($customer->customer_type) }}
                        </span>
                        @if($customer->discount_percent > 0)
                            <span class="ml-1 text-xs text-green-600">-{{ $customer->discount_percent }}%</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm">
                            <div>Limit: {{ number_format($customer->credit_limit, 2) }} zł</div>
                            <div class="{{ $customer->current_balance > 0 ? 'text-red-600' : 'text-green-600' }}">
                                Saldo: {{ number_format($customer->current_balance, 2) }} zł
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            {{ $customer->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $customer->is_active ? 'Aktywny' : 'Nieaktywny' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <a href="{{ route('customers.show', $customer) }}" class="text-blue-600 hover:text-blue-900 mr-3">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="{{ route('customers.edit', $customer) }}" class="text-yellow-600 hover:text-yellow-900 mr-3">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="{{ route('customers.orders', $customer) }}" class="text-green-600 hover:text-green-900">
                            <i class="fas fa-clipboard-list"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                        Brak klientów do wyświetlenia
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Paginacja -->
    <div class="mt-4">
        {{ $customers->links() }}
    </div>
</div>
@endsection