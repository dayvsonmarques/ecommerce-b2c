<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderDeliveredNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly Order $order)
    {
        $this->onQueue('notifications');
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject("Pedido #{$this->order->number} entregue!")
            ->greeting("Olá, {$notifiable->name}!")
            ->line("Seu pedido **#{$this->order->number}** foi entregue com sucesso.")
            ->line('Esperamos que você aproveite sua compra!')
            ->action('Avaliar Produtos', url("/orders/{$this->order->id}/review"))
            ->line('Obrigado por comprar conosco!');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'         => 'order_delivered',
            'order_id'     => $this->order->id,
            'order_number' => $this->order->number,
            'message'      => "Seu pedido #{$this->order->number} foi entregue.",
        ];
    }
}
