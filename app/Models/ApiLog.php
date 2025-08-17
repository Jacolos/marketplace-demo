<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiLog extends Model
{
    use HasFactory;

    public $timestamps = false;
    
    protected $fillable = [
        'endpoint', 'method', 'ip_address', 'customer_id',
        'request_headers', 'request_body', 'response_code',
        'response_body', 'response_time', 'created_at'
    ];

    protected $casts = [
        'request_headers' => 'array',
        'request_body' => 'array',
        'response_body' => 'array',
        'response_time' => 'float',
        'created_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($log) {
            $log->created_at = now();
        });
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function scopeRecent($query, $hours = 24)
    {
        return $query->where('created_at', '>=', now()->subHours($hours));
    }

    public function scopeByEndpoint($query, $endpoint)
    {
        return $query->where('endpoint', 'like', "%{$endpoint}%");
    }

    public function scopeErrors($query)
    {
        return $query->where('response_code', '>=', 400);
    }
}