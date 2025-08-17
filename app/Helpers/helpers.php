<?php

if (!function_exists('format_price')) {
    /**
     * Format price with currency
     */
    function format_price($amount, $currency = 'PLN')
    {
        return number_format($amount, 2, ',', ' ') . ' ' . $currency;
    }
}

if (!function_exists('format_date')) {
    /**
     * Format date in Polish format
     */
    function format_date($date, $format = 'd.m.Y H:i')
    {
        if (!$date) {
            return '-';
        }
        
        if (is_string($date)) {
            $date = \Carbon\Carbon::parse($date);
        }
        
        return $date->format($format);
    }
}

if (!function_exists('order_status_badge')) {
    /**
     * Get status badge color
     */
    function order_status_badge($status)
    {
        $badges = [
            'pending' => 'yellow',
            'processing' => 'blue',
            'shipped' => 'purple',
            'delivered' => 'green',
            'cancelled' => 'red',
        ];
        
        return $badges[$status] ?? 'gray';
    }
}

if (!function_exists('payment_status_badge')) {
    /**
     * Get payment status badge color
     */
    function payment_status_badge($status)
    {
        $badges = [
            'unpaid' => 'red',
            'paid' => 'green',
            'refunded' => 'yellow',
        ];
        
        return $badges[$status] ?? 'gray';
    }
}

if (!function_exists('calculate_tax')) {
    /**
     * Calculate tax amount
     */
    function calculate_tax($amount, $rate = 23)
    {
        return round($amount * ($rate / 100), 2);
    }
}

if (!function_exists('generate_invoice_number')) {
    /**
     * Generate invoice number
     */
    function generate_invoice_number()
    {
        $year = date('Y');
        $month = date('m');
        $count = \App\Models\Order::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->count() + 1;
            
        return sprintf('FV/%s/%s/%04d', $year, $month, $count);
    }
}
