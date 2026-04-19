<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderShippedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Order  $order,
        private readonly string $trackingCode,
        private readonly string $carrier,
    ) {
        $this->onQueue('notifications');
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject("Pedido #{$this->order->number} foi enviado!")
            ->greeting("Olá, {$notifiable->name}!")
            ->line("Seu pedido **#{$this->order->number}** foi enviado.")
            ->line("**Transportadora:** {$this->carrier}")
            ->line("**Código de rastreamento:** {$this->trackingCode}")
            ->action('Rastrear Pedido', url("/orders/{$this->order->id}"))
            ->line('Prazo estimado de entrega: 3-7 dias úteis.');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'          => 'order_shipped',
            'order_id'      => $this->order->id,
            'order_number'  => $this->order->number,
            'tracking_code' => $this->trackingCode,
            'carrier'       => $this->carrier,
            'message'       => "Seu pedido #{$this->order->number} foi enviado. Rastreamento: {$this->trackingCode}",
        ];
    }
}
