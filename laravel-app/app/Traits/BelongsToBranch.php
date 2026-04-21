<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Aplica escopo de filial automaticamente quando há uma filial ativa no contexto.
 * Models que usam este trait precisam ter a coluna branch_id.
 */
trait BelongsToBranch
{
    public static function bootBelongsToBranch(): void
    {
        static::addGlobalScope('branch', function (Builder $query): void {
            $branch = app()->bound('currentBranch') ? app('currentBranch') : null;

            if ($branch instanceof Branch) {
                $query->where($query->getModel()->getTable() . '.branch_id', $branch->id);
            }
        });

        static::creating(function ($model): void {
            if ($model->branch_id === null) {
                $branch = app()->bound('currentBranch') ? app('currentBranch') : null;
                if ($branch instanceof Branch) {
                    $model->branch_id = $branch->id;
                }
            }
        });
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
