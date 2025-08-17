<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class OrderApiController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/orders",
     *     summary="Lista zamówień",
     *     tags={"Orders"},
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function index(Request $request): Response
    {
        /** @var Customer $customer */
        $customer = $request->user(); // zakładamy, że middleware autoryzacji ustawia klienta

        $orders = $customer->orders()
            ->with(['items.product'])
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->latest()
            ->paginate($request->integer('per_page', 20));

        return response()->json([
            'success' => true,
            'data'    => $orders,
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/orders",
     *     summary="Utwórz nowe zamówienie",
     *     tags={"Orders"},
     *     @OA\Response(response=201, description="Created")
     * )
     */
    public function store(Request $request): Response
    {
        // 0) Walidacja strukturalna
        $validator = Validator::make($request->all(), [
            'items'                         => ['required', 'array', 'min:1'],
            'items.*.product_id'            => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity'              => ['required', 'integer', 'min:1'],
            'shipping_address'              => ['required', 'array'],
            'shipping_address.street'       => ['required', 'string'],
            'shipping_address.city'         => ['required', 'string'],
            'shipping_address.postal_code'  => ['required', 'string'],
            'payment_method'                => ['nullable', 'in:transfer,card,cash,deferred'],
            'notes'                         => ['nullable', 'string', 'max:500'],
            'external_id'                   => ['nullable', 'string', 'max:255'],
            'billing_address'               => ['nullable', 'array'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        /** @var Customer $customer */
        $customer = $request->user();

        // 1) Policz w pamięci przewidywaną wartość zamówienia (bez zapisu)
        $subtotal = 0;
        $itemsBlueprint = [];

        foreach ($request->input('items') as $row) {
            /** @var Product $product */
            $product  = Product::findOrFail($row['product_id']);
            $qty      = (int) $row['quantity'];
            $unitPrice= $product->getPriceForCustomer($customer);
            $lineTotal= $unitPrice * $qty; // jeśli decimal, użyj odpowiednich castów/BCMath zgodnie z modelem

            $subtotal += $lineTotal;
            $itemsBlueprint[] = compact('product', 'qty', 'unitPrice', 'lineTotal');
        }

        // Jeśli masz dodatkowe komponenty: tax/discount/shipping – policz tu spójnie z Order::calculateTotals()
        $shipping = 0;
        $discount = 0;
        $tax      = 0;
        $grand    = $subtotal + $shipping + $tax - $discount;

        // 2) NAJPIERW limit kredytowy (test wymaga komunikatu "Credit limit exceeded")
        if (!$customer->canPlaceOrder($grand)) {
            return response()->json([
                'success' => false,
                'message' => 'Credit limit exceeded',
            ], 400);
        }

        // 3) Zapis transakcyjny: tworzymy zamówienie dopiero po przejściu credit limit
        try {
            $order = DB::transaction(function () use ($request, $customer, $itemsBlueprint, $subtotal, $shipping, $discount, $tax, $grand) {
                /** @var Order $order */
                $order = Order::create([
                    'customer_id'      => $customer->id,
                    'status'           => 'pending',
                    'payment_status'   => 'unpaid',
                    'payment_method'   => $request->input('payment_method', 'transfer'),
                    'shipping_address' => $request->input('shipping_address'),
                    'billing_address'  => $request->input('billing_address', $request->input('shipping_address')),
                    'notes'            => $request->input('notes'),
                    'external_id'      => $request->input('external_id'),
                    'shipping_cost'    => $shipping,
                    'discount_amount'  => $discount,
                    'subtotal'         => $subtotal,
                    'tax_amount'       => $tax,
                    'total_amount'     => $grand,
                    'order_number'     => Order::nextNumber(),
                ]);

                // Pozycje: dopiero teraz walidujemy MOQ i stock, zapisujemy itemy i zmniejszamy stan
                foreach ($itemsBlueprint as $i) {
                    /** @var Product $product */
                    $product   = $i['product'];
                    $qty       = $i['qty'];
                    $unitPrice = $i['unitPrice'];

                    // MOQ (domyślnie 1 jeśli null)
                    $moq = $product->min_order_quantity ?? 1;
                    if ($qty < $moq) {
                        throw new \RuntimeException("Minimum order quantity for {$product->name} is {$moq}");
                    }

                    // Stock
                    if ($product->stock_quantity < $qty) {
                        throw new \RuntimeException("Insufficient stock for product: {$product->name}");
                    }

                    $orderItem = $order->items()->create([
                        'product_id'       => $product->id,
                        'quantity'         => $qty,
                        'unit_price'       => $unitPrice,
                        'discount_percent' => 0,
                        'tax_rate'         => 23,
                    ]);

                    // snapshot + zapis pozycji
                    $orderItem->createProductSnapshot();
                    $orderItem->save();

                    // zmniejsz stock
                    $product->decrementStock($qty);
                }

                // Finalizacja: przelicz totals, saldo klienta, ostatnie zamówienie
                $order->calculateTotals();
                $customer->updateBalance($order->total_amount);
                $customer->update(['last_order_at' => now()]);

                return $order->load('items.product');
            }, 3);

            // Webhook (opcjonalnie)
            if ($webhook = config('services.webhook.order_created')) {
                $this->callWebhook($webhook, $order);
            }

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'data'    => $order,
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/orders/{order}",
     *     summary="Szczegóły zamówienia",
     *     tags={"Orders"},
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function show(Request $request, string $orderNumber): Response
    {
        /** @var Customer $customer */
        $customer = $request->user();

        $order = $customer->orders()
            ->where('order_number', $orderNumber)
            ->with(['items.product.category'])
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data'    => $order,
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/orders/{order}",
     *     summary="Anuluj zamówienie",
     *     tags={"Orders"},
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function destroy(Request $request, string $orderNumber): Response
    {
        /** @var Customer $customer */
        $customer = $request->user();

        $order = $customer->orders()
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        if (!$order->canBeCancelled()) {
            return response()->json([
                'success' => false,
                'message' => 'This order cannot be cancelled',
            ], 400);
        }

        $order->cancel();

        return response()->json([
            'success' => true,
            'message' => 'Order cancelled successfully',
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/products",
     *     summary="Lista produktów",
     *     tags={"Products"},
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function products(Request $request): Response
    {
        /** @var Customer $customer */
        $customer = $request->user();

        $products = Product::active()
            ->with('category')
            ->when($request->integer('category_id'), fn($q, $categoryId) => $q->where('category_id', $categoryId))
            ->when($request->filled('search'), fn($q) => $q->search($request->input('search')))
            ->when($request->boolean('in_stock'), fn($q) => $q->inStock())
            ->paginate($request->integer('per_page', 50));

        // Dodaj ceny dla klienta
        $products->getCollection()->transform(function (Product $product) use ($customer) {
            $product->customer_price = $product->getPriceForCustomer($customer);
            return $product;
        });

        return response()->json([
            'success' => true,
            'data'    => $products,
        ]);
    }

    private function callWebhook(string $url, Order $order): void
    {
        try {
            $client = new \GuzzleHttp\Client();
            $response = $client->post($url, [
                'json' => [
                    'order_id'     => $order->id,
                    'order_number' => $order->order_number,
                    'customer_id'  => $order->customer_id,
                    'total_amount' => $order->total_amount,
                    'status'       => $order->status,
                    'created_at'   => $order->created_at->toIso8601String(),
                ],
                'timeout' => 10,
            ]);

            \Log::info('Webhook called successfully', [
                'url'           => $url,
                'order_id'      => $order->id,
                'response_code' => $response->getStatusCode(),
            ]);
        } catch (\Throwable $e) {
            \Log::error('Webhook call failed', [
                'url'      => $url,
                'order_id' => $order->id,
                'error'    => $e->getMessage(),
            ]);
        }
    }
}
