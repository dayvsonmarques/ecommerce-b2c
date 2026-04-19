<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductPrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'priceable_id',
        'priceable_type',
        'regular_price',
        'sale_price',
        'sale_starts_at',
        'sale_ends_at',
    ];

    protected $casts = [
        'regular_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'sale_starts_at' => 'datetime',
        'sale_ends_at' => 'datetime',
    ];

    public function priceable()
    {
        return $this->morphTo();
    }

    public function getActivePriceAttribute()
    {
        $now = now();

        if (
            $this->sale_price &&
            $this->sale_starts_at <= $now &&
            $this->sale_ends_at >= $now
        ) {
            return $this->sale_price;
        }

        return $this->regular_price;
    }
}
