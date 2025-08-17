<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'customer_id' => 'required|exists:customers,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.discount' => 'nullable|numeric|min:0|max:100',
            'payment_method' => 'nullable|in:transfer,card,cash,deferred',
            'shipping_address' => 'required|array',
            'shipping_address.street' => 'required|string|max:255',
            'shipping_address.city' => 'required|string|max:100',
            'shipping_address.postal_code' => 'required|string|max:10',
            'shipping_address.country' => 'nullable|string|size:2',
            'billing_address' => 'nullable|array',
            'shipping_cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ];
    }

    public function messages()
    {
        return [
            'customer_id.required' => 'Klient jest wymagany',
            'customer_id.exists' => 'Wybrany klient nie istnieje',
            'items.required' => 'Zamówienie musi zawierać przynajmniej jeden produkt',
            'items.*.product_id.required' => 'Produkt jest wymagany',
            'items.*.product_id.exists' => 'Wybrany produkt nie istnieje',
            'items.*.quantity.required' => 'Ilość jest wymagana',
            'items.*.quantity.min' => 'Minimalna ilość to 1',
            'shipping_address.street.required' => 'Ulica jest wymagana',
            'shipping_address.city.required' => 'Miasto jest wymagane',
            'shipping_address.postal_code.required' => 'Kod pocztowy jest wymagany',
        ];
    }
}