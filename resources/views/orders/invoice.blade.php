<!-- resources/views/orders/invoice.blade.php -->
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Faktura {{ $order->order_number }}</title>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { margin: 0; }
            .invoice-container { padding: 10mm; }
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            font-size: 14px; 
            line-height: 1.6;
            color: #333;
            background: #f5f5f5;
        }
        .invoice-container {
            max-width: 210mm;
            margin: 0 auto;
            padding: 20px;
            background: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header { 
            text-align: center; 
            margin-bottom: 40px;
            border-bottom: 3px solid #2563eb;
            padding-bottom: 20px;
        }
        .invoice-title { 
            font-size: 36px; 
            font-weight: bold; 
            color: #1f2937;
            margin-bottom: 10px;
        }
        .invoice-number {
            font-size: 20px;
            color: #2563eb;
            margin: 5px 0;
        }
        .invoice-date {
            font-size: 16px;
            color: #6b7280;
        }
        .company-info { 
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
            gap: 40px;
        }
        .company-section {
            flex: 1;
            padding: 20px;
            background: #f9fafb;
            border-radius: 8px;
        }
        .company-section h3 {
            color: #2563eb;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid #2563eb;
        }
        .company-section p {
            margin: 5px 0;
        }
        .company-name {
            font-weight: bold;
            font-size: 16px;
            color: #1f2937;
            margin-bottom: 10px;
        }
        .table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 30px;
        }
        .table th { 
            background-color: #2563eb;
            color: white;
            padding: 12px;
            text-align: left;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .table td { 
            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
        }
        .table tbody tr:hover {
            background-color: #f9fafb;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .totals { 
            margin-top: 30px;
            padding: 25px;
            background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
            border-radius: 8px;
            border: 1px solid #e5e7eb;
        }
        .totals-row {
            display: flex;
            justify-content: flex-end;
            margin: 8px 0;
            font-size: 15px;
        }
        .totals-label {
            margin-right: 30px;
            color: #6b7280;
        }
        .totals-value {
            min-width: 150px;
            text-align: right;
            font-weight: 500;
        }
        .total-final {
            font-size: 22px;
            font-weight: bold;
            color: #2563eb;
            border-top: 3px solid #2563eb;
            padding-top: 15px;
            margin-top: 15px;
        }
        .payment-info {
            margin-top: 40px;
            padding: 20px;
            background: #f0f9ff;
            border-left: 4px solid #2563eb;
            border-radius: 4px;
        }
        .payment-info h4 {
            color: #1f2937;
            margin-bottom: 15px;
            font-size: 16px;
        }
        .payment-row {
            display: flex;
            margin: 8px 0;
        }
        .payment-label {
            font-weight: 600;
            min-width: 150px;
            color: #4b5563;
        }
        .payment-value {
            color: #1f2937;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status-paid {
            background: #10b981;
            color: white;
        }
        .status-unpaid {
            background: #ef4444;
            color: white;
        }
        .footer { 
            margin-top: 60px;
            padding-top: 20px;
            border-top: 2px solid #e5e7eb;
            text-align: center; 
            font-size: 12px;
            color: #9ca3af;
        }
        .footer p {
            margin: 3px 0;
        }
        .no-print {
            margin-bottom: 20px;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }
        .btn {
            padding: 12px 24px;
            margin: 0 8px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 15px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }
        .btn-primary {
            background: #2563eb;
            color: white;
        }
        .btn-primary:hover {
            background: #1d4ed8;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.4);
        }
        .btn-secondary {
            background: #6b7280;
            color: white;
        }
        .btn-secondary:hover {
            background: #4b5563;
        }
        .product-name {
            font-weight: 600;
            color: #1f2937;
        }
        .product-sku {
            font-size: 11px;
            color: #9ca3af;
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()" class="btn btn-primary">
            <i class="fas fa-print"></i> Drukuj fakturę
        </button>
        <a href="{{ route('orders.show', $order) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Powrót do zamówienia
        </a>
    </div>

    <div class="invoice-container">
        <div class="header">
            <h1 class="invoice-title">FAKTURA VAT</h1>
            <div class="invoice-number">Nr: {{ $order->order_number }}</div>
            <div class="invoice-date">Data wystawienia: {{ now()->format('d.m.Y') }}</div>
        </div>

        <div class="company-info">
            <div class="company-section">
                <h3>Sprzedawca</h3>
                <p class="company-name">Jacolos Company</p>
                <p>ul. Przykładowa 123</p>
                <p>00-001 Warszawa</p>
                <p><strong>NIP:</strong> 1234567890</p>
                <p><strong>Tel:</strong> +48 22 123 45 67</p>
                <p><strong>Email:</strong> faktury@jacolos.pl</p>
            </div>
            <div class="company-section">
                <h3>Nabywca</h3>
                <p class="company-name">{{ $order->customer->company_name }}</p>
                <p>{{ $order->billing_address['street'] }}</p>
                <p>{{ $order->billing_address['postal_code'] }} {{ $order->billing_address['city'] }}</p>
                <p><strong>NIP:</strong> {{ $order->customer->nip }}</p>
                <p><strong>Tel:</strong> {{ $order->customer->phone }}</p>
                <p><strong>Email:</strong> {{ $order->customer->email }}</p>
            </div>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th style="width: 5%">Lp.</th>
                    <th style="width: 35%">Nazwa towaru/usługi</th>
                    <th style="width: 8%" class="text-center">Ilość</th>
                    <th style="width: 8%" class="text-center">Jedn.</th>
                    <th style="width: 10%" class="text-right">Cena netto</th>
                    <th style="width: 8%" class="text-center">VAT %</th>
                    <th style="width: 12%" class="text-right">Wartość netto</th>
                    <th style="width: 10%" class="text-right">Kwota VAT</th>
                    <th style="width: 14%" class="text-right">Wartość brutto</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>
                        <div class="product-name">{{ $item->product->name ?? 'Produkt usunięty' }}</div>
                        <div class="product-sku">SKU: {{ $item->product->sku ?? $item->product_snapshot['sku'] ?? 'N/A' }}</div>
                    </td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-center">{{ $item->product->unit ?? 'szt' }}</td>
                    <td class="text-right">{{ number_format($item->unit_price, 2, ',', ' ') }} zł</td>
                    <td class="text-center">{{ $item->tax_rate }}%</td>
                    <td class="text-right">{{ number_format($item->subtotal, 2, ',', ' ') }} zł</td>
                    <td class="text-right">{{ number_format($item->tax_amount, 2, ',', ' ') }} zł</td>
                    <td class="text-right"><strong>{{ number_format($item->total, 2, ',', ' ') }} zł</strong></td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals">
            @if($order->shipping_cost > 0)
            <div class="totals-row">
                <span class="totals-label">Koszt wysyłki:</span>
                <span class="totals-value">{{ number_format($order->shipping_cost, 2, ',', ' ') }} zł</span>
            </div>
            @endif
            
            @if($order->discount_amount > 0)
            <div class="totals-row">
                <span class="totals-label">Rabat:</span>
                <span class="totals-value" style="color: #ef4444;">-{{ number_format($order->discount_amount, 2, ',', ' ') }} zł</span>
            </div>
            @endif
            
            <div class="totals-row">
                <span class="totals-label">Wartość netto:</span>
                <span class="totals-value">{{ number_format($order->subtotal, 2, ',', ' ') }} zł</span>
            </div>
            
            <div class="totals-row">
                <span class="totals-label">Podatek VAT (23%):</span>
                <span class="totals-value">{{ number_format($order->tax_amount, 2, ',', ' ') }} zł</span>
            </div>
            
            <div class="totals-row total-final">
                <span class="totals-label">DO ZAPŁATY:</span>
                <span class="totals-value">{{ number_format($order->total_amount, 2, ',', ' ') }} zł</span>
            </div>
        </div>

        <div class="payment-info">
            <h4>Informacje o płatności</h4>
            <div class="payment-row">
                <span class="payment-label">Forma płatności:</span>
                <span class="payment-value">
                    @switch($order->payment_method)
                        @case('transfer') Przelew bankowy @break
                        @case('card') Karta płatnicza @break
                        @case('cash') Gotówka @break
                        @case('deferred') Płatność odroczona @break
                        @default Przelew bankowy
                    @endswitch
                </span>
            </div>
            <div class="payment-row">
                <span class="payment-label">Termin płatności:</span>
                <span class="payment-value">{{ now()->addDays(14)->format('d.m.Y') }}</span>
            </div>
            <div class="payment-row">
                <span class="payment-label">Status płatności:</span>
                <span class="payment-value">
                    <span class="status-badge {{ $order->payment_status === 'paid' ? 'status-paid' : 'status-unpaid' }}">
                        {{ $order->payment_status === 'paid' ? 'OPŁACONE' : 'NIEOPŁACONE' }}
                    </span>
                </span>
            </div>
            @if($order->payment_method === 'transfer')
            <div class="payment-row">
                <span class="payment-label">Nr konta:</span>
                <span class="payment-value">12 3456 7890 1234 5678 9012 3456</span>
            </div>
            @endif
        </div>

        <div class="footer">
            <p><strong>marketplace.jacolos.pl</strong> - System zarządzania zamówieniami B2B</p>
            <p>Faktura wygenerowana automatycznie i jest ważna bez podpisu</p>
            <p>Dziękujemy za współpracę!</p>
        </div>
    </div>
</body>
</html>