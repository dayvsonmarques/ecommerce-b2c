<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\OrderStatus;
use App\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use RuntimeException;

class Order extends Model
{
    use HasFactory, BelongsToBranch;

    protected $fillable = [
        'user_id',
        'number',
        'status',
        'subtotal',
        'shipping_cost',
        'discount_amount',
        'total',
        'coupon_code',
        'shipping_street',
        'shipping_number',
        'shipping_complement',
        'shipping_neighborhood',
        'shipping_city',
        'shipping_state',
        'shipping_postal_code',
        'tracking_code',
        'shipping_carrier',
        'paid_at',
        'shipped_at',
        'delivered_at',
        'cancelled_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status'          => OrderStatus::class,
            'subtotal'        => 'decimal:2',
            'shipping_cost'   => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total'           => 'decimal:2',
            'paid_at'         => 'datetime',
            'shipped_at'      => 'datetime',
            'delivered_at'    => 'datetime',
            'cancelled_at'    => 'datetime',
        ];
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    // -------------------------------------------------------------------------
    // State machine
    // -------------------------------------------------------------------------

    /**
     * Transiciona o pedido para um novo estado.
     *
     * @throws RuntimeException se a transição não for permitida.
     */
    public function transitionTo(OrderStatus $newStatus, ?string $changedBy = null, ?string $comment = null): void
    {
        if (! $this->status->canTransitionTo($newStatus)) {
            throw new RuntimeException(
                "Transição de '{$this->status->value}' para '{$newStatus->value}' não permitida."
            );
        }

        $oldStatus    = $this->status;
        $this->status = $newStatus;

        // Registrar timestamps de transição
        match ($newStatus) {
            OrderStatus::Paid      => $this->paid_at      = now(),
            OrderStatus::Shipped   => $this->shipped_at   = now(),
            OrderStatus::Delivered => $this->delivered_at = now(),
            OrderStatus::Cancelled => $this->cancelled_at = now(),
            default                => null,
        };

        $this->save();

        $this->statusHistories()->create([
            'from_status' => $oldStatus->value,
            'to_status'   => $newStatus->value,
            'changed_by'  => $changedBy,
            'comment'     => $comment,
        ]);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public static function generateNumber(): string
    {
        $date     = now()->format('Ymd');
        $sequence = str_pad((string) (static::whereDate('created_at', today())->count() + 1), 5, '0', STR_PAD_LEFT);

        return "ORD-{$date}-{$sequence}";
    }

    public function getShippingAddressAttribute(): array
    {
        return [
            'street'       => $this->shipping_street,
            'number'       => $this->shipping_number,
            'complement'   => $this->shipping_complement,
            'neighborhood' => $this->shipping_neighborhood,
            'city'         => $this->shipping_city,
            'state'        => $this->shipping_state,
            'postal_code'  => $this->shipping_postal_code,
        ];
    }
}
