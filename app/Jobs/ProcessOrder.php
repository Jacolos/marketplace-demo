<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class ProcessOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function handle()
    {
        // Zmień status na przetwarzanie
        $this->order->update(['status' => 'processing']);

        // Wyślij email do klienta
        Mail::to($this->order->customer->email)->send(new \App\Mail\OrderConfirmation($this->order));

        // Integracja z systemem magazynowym
        $this->syncWithWarehouse();

        // Integracja z systemem księgowym
        $this->syncWithAccounting();

        // Log
        \Log::info('Order processed', [
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
        ]);
    }

    private function syncWithWarehouse()
    {
        // Tutaj kod integracji z systemem magazynowym
        // np. przez API
    }

    private function syncWithAccounting()
    {
        // Tutaj kod integracji z systemem księgowym
        // np. przez API
    }
}