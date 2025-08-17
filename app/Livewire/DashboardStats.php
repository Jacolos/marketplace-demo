<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Order;
use App\Models\Product;
use App\Models\Customer;
use Illuminate\Support\Facades\Cache;

class DashboardStats extends Component
{
    public $period = 'today';
    public $stats = [];
    public $chartData = [];
    public $refreshInterval = 60; // sekundy

    protected $listeners = ['refreshStats' => '$refresh'];

    public function mount()
    {
        $this->loadStats();
    }

    public function updatedPeriod()
    {
        $this->loadStats();
    }

    public function loadStats()
    {
        $cacheKey = "dashboard_stats_{$this->period}";
        
        $this->stats = Cache::remember($cacheKey, $this->refreshInterval, function () {
            return $this->calculateStats();
        });

        $this->chartData = $this->prepareChartData();
    }

    private function calculateStats()
    {
        $query = Order::query();
        
        switch ($this->period) {
            case 'today':
                $query->whereDate('created_at', today());
                break;
            case 'week':
                $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                break;
            case 'month':
                $query->whereMonth('created_at', now()->month)
                      ->whereYear('created_at', now()->year);
                break;
            case 'year':
                $query->whereYear('created_at', now()->year);
                break;
        }

        $orders = clone $query;
        $revenue = clone $query;
        
        return [
            'orders_count' => $orders->count(),
            'revenue' => $revenue->where('payment_status', 'paid')->sum('total_amount'),
            'avg_order_value' => $orders->avg('total_amount') ?? 0,
            'new_customers' => Customer::when($this->period === 'today', function ($q) {
                    $q->whereDate('created_at', today());
                })
                ->when($this->period === 'week', function ($q) {
                    $q->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                })
                ->when($this->period === 'month', function ($q) {
                    $q->whereMonth('created_at', now()->month);
                })
                ->count(),
            'products_sold' => $orders->with('items')->get()
                ->pluck('items')->flatten()
                ->sum('quantity'),
            'conversion_rate' => $this->calculateConversionRate(),
        ];
    }

    private function calculateConversionRate()
    {
        // Symulacja - normalnie byłoby to oparte na rzeczywistych danych o sesjach
        return rand(2, 8) . '.' . rand(0, 99);
    }

    private function prepareChartData()
    {
        $data = [];
        
        switch ($this->period) {
            case 'today':
                // Dane godzinowe
                for ($i = 0; $i < 24; $i++) {
                    $hour = str_pad($i, 2, '0', STR_PAD_LEFT);
                    $data['labels'][] = "{$hour}:00";
                    $data['values'][] = Order::whereDate('created_at', today())
                        ->whereTime('created_at', '>=', "{$hour}:00:00")
                        ->whereTime('created_at', '<', ($i + 1) . ":00:00")
                        ->sum('total_amount');
                }
                break;
                
            case 'week':
                // Dane dzienne
                for ($i = 0; $i < 7; $i++) {
                    $date = now()->startOfWeek()->addDays($i);
                    $data['labels'][] = $date->format('D');
                    $data['values'][] = Order::whereDate('created_at', $date)->sum('total_amount');
                }
                break;
                
            case 'month':
                // Dane tygodniowe
                $weeksInMonth = ceil(now()->daysInMonth / 7);
                for ($i = 1; $i <= $weeksInMonth; $i++) {
                    $data['labels'][] = "Tydzień {$i}";
                    $startDate = now()->startOfMonth()->addWeeks($i - 1);
                    $endDate = $startDate->copy()->addWeek();
                    $data['values'][] = Order::whereBetween('created_at', [$startDate, $endDate])
                        ->sum('total_amount');
                }
                break;
                
            case 'year':
                // Dane miesięczne
                for ($i = 1; $i <= 12; $i++) {
                    $data['labels'][] = now()->month($i)->format('M');
                    $data['values'][] = Order::whereMonth('created_at', $i)
                        ->whereYear('created_at', now()->year)
                        ->sum('total_amount');
                }
                break;
        }
        
        return $data;
    }

    public function refreshStats()
    {
        Cache::forget("dashboard_stats_{$this->period}");
        $this->loadStats();
    }

    public function render()
    {
        return view('livewire.dashboard-stats');
    }
}