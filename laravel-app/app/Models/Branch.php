<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Branch extends Model
{
    protected $connection = 'tenant';
    protected $table = 'branches';

    protected $fillable = [
        'name',
        'code',
        'phone',
        'email',
        'street',
        'number',
        'complement',
        'neighborhood',
        'city',
        'state',
        'postal_code',
        'is_active',
        'is_headquarters',
        'settings',
    ];

    protected $casts = [
        'is_active'       => 'boolean',
        'is_headquarters' => 'boolean',
        'settings'        => 'array',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class);
    }
}
