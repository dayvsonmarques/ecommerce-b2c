<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\OrderDelivered;
use App\Notifications\OrderDeliveredNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendOrderDeliveredNotification implements ShouldQueue
{
    public string $queue = 'notifications';

    public function handle(OrderDelivered $event): void
    {
        $event->order->user->notify(new OrderDeliveredNotification($event->order));
    }
}
