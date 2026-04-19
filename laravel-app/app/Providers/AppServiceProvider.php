<?php

declare(strict_types=1);

namespace App\Providers;

use App\Events\OrderCancelled;
use App\Events\OrderCreated;
use App\Events\OrderDelivered;
use App\Events\OrderShipped;
use App\Listeners\RestoreStockOnCancellation;
use App\Listeners\SendOrderConfirmedNotification;
use App\Listeners\SendOrderDeliveredNotification;
use App\Listeners\SendOrderShippedNotification;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Event::listen(OrderCreated::class,   SendOrderConfirmedNotification::class);
        Event::listen(OrderShipped::class,   SendOrderShippedNotification::class);
        Event::listen(OrderDelivered::class, SendOrderDeliveredNotification::class);
        Event::listen(OrderCancelled::class, RestoreStockOnCancellation::class);
    }
}
