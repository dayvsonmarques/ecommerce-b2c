<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class ProductSku extends Model
{
    use HasFactory, BelongsToBranch;

    protected $fillable = [
        'product_id',
        'sku',
        'price',
        'quantity',
        'variation_values',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'variation_values' => 'json',
        'is_active' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function prices(): MorphMany
    {
        return $this->morphMany(ProductPrice::class, 'priceable');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInStock($query)
    {
        return $query->where('quantity', '>', 0);
    }
}
