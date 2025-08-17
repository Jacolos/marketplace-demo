<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::query();

        // Filtry
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('company_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%")
                  ->orWhere('contact_person', 'like', "%{$search}%");
            });
        }

        if ($request->filled('customer_type')) {
            $query->where('customer_type', $request->customer_type);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        // Sortowanie
        $sortBy = $request->get('sort_by', 'company_name');
        $sortDirection = $request->get('sort_direction', 'asc');
        $query->orderBy($sortBy, $sortDirection);

        $customers = $query->paginate(20);

        // Statystyki
        $stats = [
            'total_customers' => Customer::count(),
            'active_customers' => Customer::where('is_active', true)->count(),
            'vip_customers' => Customer::where('customer_type', 'vip')->count(),
            'total_debt' => Customer::sum('current_balance'),
        ];

        return view('customers.index', compact('customers', 'stats'));
    }

    public function create()
    {
        return view('customers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'nip' => 'required|string|size:10|unique:customers',
            'email' => 'required|email|unique:customers',
            'phone' => 'required|string|max:20',
            'contact_person' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'postal_code' => 'required|string|max:10',
            'country' => 'required|string|size:2',
            'customer_type' => 'required|in:retail,wholesale,vip',
            'credit_limit' => 'required|numeric|min:0',
            'discount_percent' => 'required|integer|min:0|max:100',
        ]);

        $validated['api_key'] = Str::random(32);
        $validated['is_active'] = true;
        $validated['current_balance'] = 0;

        $customer = Customer::create($validated);

        return redirect()->route('customers.show', $customer)
            ->with('success', 'Klient został utworzony pomyślnie.');
    }

    public function show(Customer $customer)
    {
        // Statystyki klienta
        $stats = [
            'total_orders' => $customer->orders()->count(),
            'pending_orders' => $customer->orders()->where('status', 'pending')->count(),
            'total_spent' => $customer->orders()->where('payment_status', 'paid')->sum('total_amount'),
            'average_order_value' => $customer->orders()->where('payment_status', 'paid')->avg('total_amount'),
            'available_credit' => $customer->getAvailableCredit(),
            'last_order_date' => $customer->last_order_at,
        ];

        // Ostatnie zamówienia
        $recentOrders = $customer->orders()
            ->with('items.product')
            ->latest()
            ->limit(10)
            ->get();

        // Historia płatności
        $paymentHistory = $customer->orders()
            ->whereNotNull('payment_status')
            ->select('payment_status', 'total_amount', 'created_at')
            ->latest()
            ->limit(20)
            ->get();

        // Aktywność API
        $apiActivity = $customer->apiLogs()
            ->select('endpoint', 'method', 'response_code', 'created_at')
            ->latest()
            ->limit(20)
            ->get();

        return view('customers.show', compact('customer', 'stats', 'recentOrders', 'paymentHistory', 'apiActivity'));
    }

    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'email' => 'required|email|unique:customers,email,' . $customer->id,
            'phone' => 'required|string|max:20',
            'contact_person' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'postal_code' => 'required|string|max:10',
            'country' => 'required|string|size:2',
            'customer_type' => 'required|in:retail,wholesale,vip',
            'credit_limit' => 'required|numeric|min:0',
            'discount_percent' => 'required|integer|min:0|max:100',
            'is_active' => 'boolean',
        ]);

        $customer->update($validated);

        return redirect()->route('customers.show', $customer)
            ->with('success', 'Dane klienta zostały zaktualizowane.');
    }

    public function destroy(Customer $customer)
    {
        // Sprawdź czy klient ma zamówienia
        if ($customer->orders()->exists()) {
            return back()->with('error', 'Nie można usunąć klienta, który ma zamówienia. Możesz go dezaktywować.');
        }

        $customer->delete();

        return redirect()->route('customers.index')
            ->with('success', 'Klient został usunięty.');
    }

    public function regenerateApiKey(Customer $customer)
    {
        $newKey = $customer->regenerateApiKey();
        
        return back()->with('success', 'Klucz API został wygenerowany ponownie: ' . $newKey);
    }

    public function orders(Customer $customer)
    {
        $orders = $customer->orders()
            ->with('items.product')
            ->latest()
            ->paginate(20);

        return view('customers.orders', compact('customer', 'orders'));
    }

    public function updateCreditLimit(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'credit_limit' => 'required|numeric|min:0',
        ]);

        $customer->update($validated);

        return back()->with('success', 'Limit kredytowy został zaktualizowany.');
    }

    public function clearBalance(Customer $customer)
    {
        $customer->update(['current_balance' => 0]);
        
        // Oznacz wszystkie niezapłacone zamówienia jako zapłacone
        $customer->orders()
            ->where('payment_status', 'unpaid')
            ->update(['payment_status' => 'paid']);

        return back()->with('success', 'Saldo klienta zostało wyczyszczone.');
    }
}