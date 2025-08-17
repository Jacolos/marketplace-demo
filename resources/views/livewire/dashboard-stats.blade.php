<div>
    <!-- Wybór okresu -->
    <div class="mb-4">
        <select wire:model="period" class="px-3 py-2 border rounded focus:outline-none focus:border-blue-500">
            <option value="today">Dzisiaj</option>
            <option value="week">Ten tydzień</option>
            <option value="month">Ten miesiąc</option>
            <option value="year">Ten rok</option>
        </select>
        <button wire:click="refreshStats" class="ml-2 text-blue-600 hover:text-blue-800">
            <i class="fas fa-sync-alt"></i> Odśwież
        </button>
    </div>

    <!-- Statystyki -->
    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-gray-500 text-sm">Zamówienia</p>
            <p class="text-2xl font-bold">{{ $stats['orders_count'] ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-gray-500 text-sm">Przychód</p>
            <p class="text-2xl font-bold">{{ number_format($stats['revenue'] ?? 0, 2) }} zł</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-gray-500 text-sm">Śr. wartość</p>
            <p class="text-2xl font-bold">{{ number_format($stats['avg_order_value'] ?? 0, 2) }} zł</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-gray-500 text-sm">Nowi klienci</p>
            <p class="text-2xl font-bold">{{ $stats['new_customers'] ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-gray-500 text-sm">Sprzedano szt.</p>
            <p class="text-2xl font-bold">{{ $stats['products_sold'] ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-gray-500 text-sm">Konwersja</p>
            <p class="text-2xl font-bold">{{ $stats['conversion_rate'] ?? 0 }}%</p>
        </div>
    </div>

    <!-- Wykres -->
    @if(isset($chartData['labels']) && count($chartData['labels']) > 0)
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold mb-4">Wykres sprzedaży</h3>
        <canvas id="statsChart" height="100"></canvas>
    </div>

    <script>
        document.addEventListener('livewire:load', function () {
            const ctx = document.getElementById('statsChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: @json($chartData['labels']),
                    datasets: [{
                        label: 'Sprzedaż (zł)',
                        data: @json($chartData['values']),
                        borderColor: 'rgb(59, 130, 246)',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
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
        });
    </script>
    @endif
</div>