<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Customer;

class ApiAuthentication
{
    public function handle(Request $request, Closure $next)
    {
        $apiKey = $request->header('X-API-Key') ?? $request->input('api_key');
        
        if (!$apiKey) {
            return response()->json([
                'success' => false,
                'message' => 'API key is required',
            ], 401);
        }

        $customer = Customer::where('api_key', $apiKey)
            ->where('is_active', true)
            ->first();

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid API key',
            ], 401);
        }

        // Dodaj klienta do request
        $request->merge(['customer' => $customer]);
        $request->setUserResolver(function () use ($customer) {
            return $customer;
        });

        return $next($request);
    }
}