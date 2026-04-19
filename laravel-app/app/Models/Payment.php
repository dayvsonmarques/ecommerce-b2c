<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'gateway',
        'gateway_payment_id',
        'gateway_charge_id',
        'method',
        'status',
        'amount',
        'installments',
        'boleto_url',
        'boleto_barcode',
        'pix_qr_code',
        'pix_qr_code_text',
        'pix_expires_at',
        'paid_at',
        'gateway_response',
    ];

    protected function casts(): array
    {
        return [
            'amount'           => 'decimal:2',
            'installments'     => 'integer',
            'pix_expires_at'   => 'datetime',
            'paid_at'          => 'datetime',
            'gateway_response' => 'array',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }
}
