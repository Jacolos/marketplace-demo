<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id', 'sku', 'name', 'description', 'price', 'wholesale_price',
        'stock_quantity', 'min_order_quantity', 'unit', 'images', 'attributes',
        'is_active', 'is_featured', 'views_count'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'wholesale_price' => 'decimal:2',
        'stock_quantity' => 'integer',
        'min_order_quantity' => 'integer',
        'images' => 'array',
        'attributes' => 'array',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'views_count' => 'integer',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeInStock($query)
    {
        return $query->where('stock_quantity', '>', 0);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function (Builder $q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('sku', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }

    public function getPriceForCustomer(Customer $customer)
    {
        // Określ cenę bazową
        $price = $this->price;
        
        // Jeśli klient jest hurtowy i istnieje cena hurtowa, użyj jej
        if ($customer->customer_type === 'wholesale' && $this->wholesale_price && $this->wholesale_price > 0) {
            $price = $this->wholesale_price;
        }
        
        // VIP może mieć specjalne warunki
        if ($customer->customer_type === 'vip' && $this->wholesale_price && $this->wholesale_price > 0) {
            // VIP dostaje najlepszą cenę
            $price = min($this->wholesale_price, $this->price);
        }

        // Zastosuj rabat klienta
        if ($customer->discount_percent > 0) {
            $discount = $price * ($customer->discount_percent / 100);
            $price = $price - $discount;
        }

        // Upewnij się, że cena nie jest ujemna
        return max(0, round($price, 2));
    }

    public static function calculatePriceForCustomer($productId, $customerId)
    {
        $product = self::find($productId);
        $customer = Customer::find($customerId);
        
        if (!$product || !$customer) {
            return 0;
        }
        
        return $product->getPriceForCustomer($customer);
    }

    public function decrementStock($quantity)
    {
        return $this->decrement('stock_quantity', $quantity);
    }

    public function incrementStock($quantity)
    {
        return $this->increment('stock_quantity', $quantity);
    }
}