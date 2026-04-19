<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\OrderCancelled;
use App\Services\StockService;
use Illuminate\Contracts\Queue\ShouldQueue;

class RestoreStockOnCancellation implements ShouldQueue
{
    public string $queue = 'default';

    public function __construct(private readonly StockService $stockService)
    {
    }

    public function handle(OrderCancelled $event): void
    {
        foreach ($event->order->items as $item) {
            $this->stockService->restore(
                $item->product_id,
                $item->product_sku_id,
                $item->quantity,
            );
        }
    }
}
