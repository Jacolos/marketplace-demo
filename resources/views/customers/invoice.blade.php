<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Faktura {{ $order->order_number }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 30px; }
        .invoice-title { font-size: 24px; font-weight: bold; }
        .invoice-details { margin-bottom: 20px; }
        .company-info { margin-bottom: 30px; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .table th, .table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .table th { background-color: #f4f4f4; }
        .text-right { text-align: right; }
        .totals { text-align: right; margin-top: 20px; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1 class="invoice-title">FAKTURA VAT</h1>
        <p>Nr: {{ $order->order_number }}</p>
        <p>Data wystawienia: {{ now()->format('d.m.Y') }}</p>
    </div>

    <div class="company-info">
        <table style="width: 100%">
            <tr>
                <td style="width: 50%">
                    <strong>Sprzedawca:</strong><br>
                    CStore S.A.<br>
                    ul. Przykładowa 123<br>
                    00-001 Warszawa<br>
                    NIP: 1234567890
                </td>
                <td style="width: 50%">
                    <strong>Nabywca:</strong><br>
                    {{ $order->customer->company_name }}<br>
                    {{ $order->billing_address['street'] }}<br>
                    {{ $order->billing_address['postal_code'] }} {{ $order->billing_address['city'] }}<br>
                    NIP: {{ $order->customer->nip }}
                </td>
            </tr>
        </table>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Lp.</th>
                <th>Nazwa towaru/usługi</th>
                <th>Ilość</th>
                <th>Jedn.</th>
                <th>Cena netto</th>
                <th>VAT %</th>
                <th>Wartość netto</th>
                <th>Wartość VAT</th>
                <th>Wartość brutto</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item->product->name }}</td>
                <td class="text-right">{{ $item->quantity }}</td>
                <td>{{ $item->product->unit }}</td>
                <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                <td class="text-right">{{ $item->tax_rate }}%</td>
                <td class="text-right">{{ number_format($item->subtotal, 2) }}</td>
                <td class="text-right">{{ number_format($item->tax_amount, 2) }}</td>
                <td class="text-right">{{ number_format($item->total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6" class="text-right"><strong>RAZEM:</strong></td>
                <td class="text-right"><strong>{{ number_format($order->subtotal, 2) }}</strong></td>
                <td class="text-right"><strong>{{ number_format($order->tax_amount, 2) }}</strong></td>
                <td class="text-right"><strong>{{ number_format($order->total_amount, 2) }}</strong></td>
            </tr>
        </tfoot>
    </table>

    <div class="totals">
        <p><strong>Do zapłaty: {{ number_format($order->total_amount, 2) }} PLN</strong></p>
        <p>Słownie: {{ $order->total_amount }} złotych</p>
        <p>Termin płatności: {{ now()->addDays(14)->format('d.m.Y') }}</p>
        <p>Forma płatności: {{ ucfirst($order->payment_method ?? 'przelew') }}</p>
    </div>

    <div class="footer">
        <p>Faktura wygenerowana automatycznie przez system CStore Marketplace</p>
    </div>
</body>
</html>