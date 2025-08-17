<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use GuzzleHttp\Client;
use App\Models\Product;

class SyncWithExternalApi implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        $client = new Client();
        
        try {
            // Przykład synchronizacji produktów z zewnętrznym API
            $response = $client->get('https://api.external-system.com/products', [
                'headers' => [
                    'Authorization' => 'Bearer ' . config('services.external.api_key'),
                ],
            ]);

            $products = json_decode($response->getBody(), true);

            foreach ($products['data'] as $productData) {
                Product::updateOrCreate(
                    ['sku' => $productData['sku']],
                    [
                        'name' => $productData['name'],
                        'description' => $productData['description'],
                        'price' => $productData['price'],
                        'stock_quantity' => $productData['stock'],
                    ]
                );
            }

            \Log::info('External API sync completed', [
                'products_synced' => count($products['data']),
            ]);
            
        } catch (\Exception $e) {
            \Log::error('External API sync failed', [
                'error' => $e->getMessage(),
            ]);
            
            // Ponów próbę
            $this->release(300); // Spróbuj ponownie za 5 minut
        }
    }
}