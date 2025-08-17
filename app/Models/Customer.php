<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_name', 'nip', 'email', 'phone', 'contact_person',
        'address', 'city', 'postal_code', 'country', 'credit_limit',
        'current_balance', 'customer_type', 'discount_percent',
        'is_active', 'api_key', 'last_order_at'
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'discount_percent' => 'integer',
        'is_active' => 'boolean',
        'last_order_at' => 'datetime',
    ];

    protected $hidden = [
        'api_key',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($customer) {
            if (empty($customer->api_key)) {
                $customer->api_key = Str::random(32);
            }
        });
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function apiLogs()
    {
        return $this->hasMany(ApiLog::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('customer_type', $type);
    }

    public function getAvailableCredit()
    {
        return $this->credit_limit - $this->current_balance;
    }

    public function canPlaceOrder($orderAmount)
    {
        return $this->is_active && ($this->current_balance + $orderAmount <= $this->credit_limit);
    }

    public function updateBalance($amount)
    {
        $this->current_balance += $amount;
        $this->save();
    }

    public function regenerateApiKey()
    {
        $this->api_key = Str::random(32);
        $this->save();
        return $this->api_key;
    }
}