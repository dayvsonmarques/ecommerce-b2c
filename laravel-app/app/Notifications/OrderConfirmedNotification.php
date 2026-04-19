<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderConfirmedNotification extends Notification implements ShouldQueue
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
            ->subject("Pedido #{$this->order->number} confirmado!")
            ->greeting("Olá, {$notifiable->name}!")
            ->line("Recebemos seu pedido **#{$this->order->number}** com sucesso.")
            ->line("**Total:** R$ " . number_format((float) $this->order->total, 2, ',', '.'))
            ->line("**Status:** {$this->order->status->label()}")
            ->action('Ver Pedido', url("/orders/{$this->order->id}"))
            ->line('Obrigado pela sua compra!');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'         => 'order_confirmed',
            'order_id'     => $this->order->id,
            'order_number' => $this->order->number,
            'total'        => $this->order->total,
            'message'      => "Seu pedido #{$this->order->number} foi confirmado.",
        ];
    }
}
