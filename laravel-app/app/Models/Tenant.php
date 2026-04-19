<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    protected $connection = 'central';
    protected $table = 'tenants';

    protected $fillable = [
        'name',
        'slug',
        'database',
        'plan',
        'is_active',
        'settings',
        'trial_ends_at',
    ];

    protected $casts = [
        'is_active'     => 'boolean',
        'settings'      => 'array',
        'trial_ends_at' => 'datetime',
    ];

    public function domains(): HasMany
    {
        return $this->hasMany(TenantDomain::class);
    }

    public function getDatabaseName(): string
    {
        return config('tenancy.database_prefix') . $this->slug;
    }
}
