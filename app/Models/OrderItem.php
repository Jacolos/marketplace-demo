<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id', 'product_id', 'quantity', 'unit_price',
        'discount_percent', 'tax_rate', 'subtotal', 'tax_amount',
        'total', 'product_snapshot'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'product_snapshot' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($item) {
            $item->calculateAmounts();
        });

        static::updating(function ($item) {
            $item->calculateAmounts();
        });
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function calculateAmounts()
    {
        $discountedPrice = $this->unit_price * (1 - $this->discount_percent / 100);
        $this->subtotal = $discountedPrice * $this->quantity;
        $this->tax_amount = $this->subtotal * ($this->tax_rate / 100);
        $this->total = $this->subtotal + $this->tax_amount;
    }

    public function createProductSnapshot()
    {
        if ($this->product) {
            $this->product_snapshot = [
                'sku' => $this->product->sku,
                'name' => $this->product->name,
                'description' => $this->product->description,
                'category' => $this->product->category->name,
            ];
        }
    }
}
