<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LowStockNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly Product $product)
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
            ->subject("[Alerta] Estoque baixo: {$this->product->name}")
            ->error()
            ->greeting('Alerta de estoque baixo!')
            ->line("O produto **{$this->product->name}** (SKU: {$this->product->sku}) está com estoque baixo.")
            ->line("**Quantidade atual:** {$this->product->quantity} unidades")
            ->line("**Alerta mínimo configurado:** {$this->product->min_quantity_alert} unidades")
            ->action('Gerenciar Estoque', url("/admin/products/{$this->product->id}"));
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'        => 'low_stock',
            'product_id'  => $this->product->id,
            'product_name'=> $this->product->name,
            'sku'         => $this->product->sku,
            'quantity'    => $this->product->quantity,
            'min_alert'   => $this->product->min_quantity_alert,
            'message'     => "Estoque baixo: {$this->product->name} ({$this->product->quantity} unidades restantes)",
        ];
    }
}
