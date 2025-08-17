<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'status' => 'sometimes|in:pending,processing,shipped,delivered,cancelled',
            'payment_status' => 'sometimes|in:unpaid,paid,refunded',
            'payment_method' => 'sometimes|in:transfer,card,cash,deferred',
            'notes' => 'nullable|string|max:500',
            'shipping_cost' => 'sometimes|numeric|min:0',
            'discount_amount' => 'sometimes|numeric|min:0',
        ];
    }
}