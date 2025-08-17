@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="fade-in">
    <!-- Page header -->
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900">Dashboard</h2>
        <p class="text-gray-600">Przegląd systemu zarządzania zamówieniami</p>
    </div>

    <!-- Stats cards - Responsive grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <!-- Total Orders -->
        <div class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Wszystkie zamówienia</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total_orders'] }}</p>
                </div>
                <div class="bg-blue-100 p-3 rounded-full">
                    <i class="fas fa-shopping-cart text-blue-600"></i>
                </div>
            </div>
        </div>

        <!-- Pending Orders -->
        <div class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Oczekujące</p>
                    <p class="text-2xl font-bold text-yellow-600">{{ $stats['pending_orders'] }}</p>
                </div>
                <div class="bg-yellow-100 p-3 rounded-full">
                    <i class="fas fa-clock text-yellow-600"></i>
                </div>
            </div>
        </div>

        <!-- Revenue Today -->
        <div class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Przychód dziś</p>
                    <p class="text-2xl font-bold text-green-600">{{ number_format($stats['revenue_today'], 2) }} zł</p>
                </div>
                <div class="bg-green-100 p-3 rounded-full">
                    <i class="fas fa-chart-line text-green-600"></i>
                </div>
            </div>
        </div>

        <!-- Active Customers -->
        <div class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Aktywni klienci</p>
                    <p class="text-2xl font-bold text-purple-600">{{ $stats['total_customers'] }}</p>
                </div>
                <div class="bg-purple-100 p-3 rounded-full">
                    <i class="fas fa-users text-purple-600"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts and tables - Responsive layout -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Sales Chart -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Sprzedaż (ostatnie 30 dni)</h3>
            <div class="chart-container">
                <canvas id="salesChart"></canvas>
            </div>
        </div>

        <!-- Top Products -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Najpopularniejsze produkty</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="border-b">
                            <th class="text-left py-2">Produkt</th>
                            <th class="text-right py-2">Sprzedano</th>
                            <th class="text-right py-2">Przychód</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($topProducts as $product)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-2">
                                <div>
                                    <p class="font-medium">{{ $product->name }}</p>
                                    <p class="text-sm text-gray-500">{{ $product->sku }}</p>
                                </div>
                            </td>
                            <td class="text-right py-2">{{ $product->total_sold }} szt</td>
                            <td class="text-right py-2">{{ number_format($product->total_sold * $product->price, 2) }} zł</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Recent Orders - Responsive table -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold mb-4">Ostatnie zamówienia</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full responsive-table">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left">Nr zamówienia</th>
                        <th class="px-4 py-2 text-left">Klient</th>
                        <th class="px-4 py-2 text-left">Status</th>
                        <th class="px-4 py-2 text-right">Wartość</th>
                        <th class="px-4 py-2 text-left">Data</th>
                        <th class="px-4 py-2 text-center">Akcje</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentOrders as $order)
                    <tr class="border-b hover:bg-gray-50">
                        <td data-label="Nr zamówienia" class="px-4 py-2">
                            <a href="/orders/{{ $order->id }}" class="text-blue-600 hover:underline">
                                {{ $order->order_number }}
                            </a>
                        </td>
                        <td data-label="Klient" class="px-4 py-2">
                            <div>
                                <p class="font-medium">{{ $order->customer->company_name }}</p>
                                <p class="text-sm text-gray-500 hidden sm:block">{{ $order->customer->email }}</p>
                            </div>
                        </td>
                        <td data-label="Status" class="px-4 py-2">
                            <span class="px-2 py-1 text-xs rounded-full 
                                @if($order->status === 'pending') bg-yellow-100 text-yellow-800
                                @elseif($order->status === 'processing') bg-blue-100 text-blue-800
                                @elseif($order->status === 'shipped') bg-purple-100 text-purple-800
                                @elseif($order->status === 'delivered') bg-green-100 text-green-800
                                @else bg-red-100 text-red-800
                                @endif">
                                {{ ucfirst($order->status) }}
                            </span>
                        </td>
                        <td data-label="Wartość" class="px-4 py-2 text-right">
                            {{ number_format($order->total_amount, 2) }} zł
                        </td>
                        <td data-label="Data" class="px-4 py-2">
                            {{ $order->created_at->format('d.m.Y H:i') }}
                        </td>
                        <td data-label="Akcje" class="px-4 py-2 text-center">
                            <a href="/orders/{{ $order->id }}" class="text-blue-600 hover:text-blue-800">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Przygotuj dane dla wykresu
    const chartData = @json($salesChart);
    
    // Jeśli są dane, stwórz wykres
    if(chartData && chartData.length > 0) {
        const ctx = document.getElementById('salesChart').getContext('2d');
        
        // Przygotuj etykiety i wartości
        const labels = chartData.map(item => {
            const date = new Date(item.date);
            return date.getDate() + '.' + (date.getMonth() + 1);
        });
        
        const values = chartData.map(item => parseFloat(item.total_sales) || 0);
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Sprzedaż (zł)',
                    data: values,
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Sprzedaż: ' + context.parsed.y.toFixed(2) + ' zł';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString('pl-PL') + ' zł';
                            }
                        }
                    }
                }
            }
        });
    } else {
        // Jeśli brak danych, pokaż komunikat
        document.getElementById('salesChart').parentElement.innerHTML = 
            '<p class="text-gray-500 text-center py-8">Brak danych do wyświetlenia</p>';
    }
});
</script>
@endsection
