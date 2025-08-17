<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'category_id' => 'required|exists:categories,id',
            'sku' => 'nullable|string|max:50|unique:products',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'wholesale_price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'min_order_quantity' => 'required|integer|min:1',
            'unit' => 'required|string|max:20',
            'images.*' => 'nullable|image|max:2048',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ];
    }

    public function messages()
    {
        return [
            'category_id.required' => 'Kategoria jest wymagana',
            'name.required' => 'Nazwa produktu jest wymagana',
            'price.required' => 'Cena jest wymagana',
            'price.min' => 'Cena nie może być ujemna',
            'stock_quantity.required' => 'Stan magazynowy jest wymagany',
            'sku.unique' => 'Ten SKU już istnieje',
            'images.*.image' => 'Plik musi być obrazem',
            'images.*.max' => 'Obraz nie może być większy niż 2MB',
        ];
    }
}