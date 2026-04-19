<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\OrderShipped;
use App\Notifications\OrderShippedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendOrderShippedNotification implements ShouldQueue
{
    public string $queue = 'notifications';

    public function handle(OrderShipped $event): void
    {
        $event->order->user->notify(
            new OrderShippedNotification($event->order, $event->trackingCode, $event->carrier)
        );
    }
}
