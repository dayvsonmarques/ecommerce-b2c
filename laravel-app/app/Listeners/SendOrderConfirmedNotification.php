<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Notifications\OrderConfirmedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendOrderConfirmedNotification implements ShouldQueue
{
    public string $queue = 'notifications';

    public function handle(OrderCreated $event): void
    {
        $event->order->user->notify(new OrderConfirmedNotification($event->order));
    }
}
