<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'short_description',
        'price',
        'cost_price',
        'sku',
        'quantity',
        'min_quantity_alert',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price'              => 'decimal:2',
            'cost_price'         => 'decimal:2',
            'quantity'           => 'integer',
            'min_quantity_alert' => 'integer',
            'is_active'          => 'boolean',
        ];
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function variations(): HasMany
    {
        return $this->hasMany(ProductVariation::class);
    }

    public function skus(): HasMany
    {
        return $this->hasMany(ProductSku::class);
    }

    public function prices(): MorphMany
    {
        return $this->morphMany(ProductPrice::class, 'priceable');
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInStock($query)
    {
        return $query->where('quantity', '>', 0);
    }

    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('description', 'like', "%{$term}%")
              ->orWhere('sku', 'like', "%{$term}%");
        });
    }

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    /**
     * Preço de venda vigente (considera promoção ativa).
     */
    public function getCurrentPriceAttribute(): string
    {
        $activePrice = $this->prices()
            ->where(function ($q) {
                $q->whereNull('sale_ends_at')
                  ->orWhere('sale_ends_at', '>=', now());
            })
            ->where(function ($q) {
                $q->whereNull('sale_starts_at')
                  ->orWhere('sale_starts_at', '<=', now());
            })
            ->whereNotNull('sale_price')
            ->first();

        return $activePrice?->sale_price ?? $this->price;
    }

    public function isLowStock(): bool
    {
        return $this->quantity <= $this->min_quantity_alert;
    }
}
