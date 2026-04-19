<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'product_sku_id',
        'product_name',
        'product_sku',
        'quantity',
        'unit_price',
        'total_price',
        'variation_values',
    ];

    protected function casts(): array
    {
        return [
            'quantity'         => 'integer',
            'unit_price'       => 'decimal:2',
            'total_price'      => 'decimal:2',
            'variation_values' => 'array',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function sku(): BelongsTo
    {
        return $this->belongsTo(ProductSku::class, 'product_sku_id');
    }
}
