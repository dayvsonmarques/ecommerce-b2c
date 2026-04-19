<?php

declare(strict_types=1);

namespace App\Enums;

enum OrderStatus: string
{
    case Pending    = 'pending';
    case Paid       = 'paid';
    case Processing = 'processing';
    case Shipped    = 'shipped';
    case Delivered  = 'delivered';
    case Cancelled  = 'cancelled';
    case Refunded   = 'refunded';

    /** Transições válidas a partir de cada estado. */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Pending    => [self::Paid, self::Cancelled],
            self::Paid       => [self::Processing, self::Refunded],
            self::Processing => [self::Shipped, self::Cancelled],
            self::Shipped    => [self::Delivered],
            self::Delivered  => [],
            self::Cancelled  => [],
            self::Refunded   => [],
        };
    }

    public function canTransitionTo(self $next): bool
    {
        return in_array($next, $this->allowedTransitions(), true);
    }

    public function label(): string
    {
        return match ($this) {
            self::Pending    => 'Aguardando pagamento',
            self::Paid       => 'Pago',
            self::Processing => 'Em processamento',
            self::Shipped    => 'Enviado',
            self::Delivered  => 'Entregue',
            self::Cancelled  => 'Cancelado',
            self::Refunded   => 'Reembolsado',
        };
    }
}
