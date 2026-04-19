<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductSkuResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sku' => $this->sku,
            'price' => $this->price ?? $this->product?->price,
            'quantity' => $this->quantity,
            'is_active' => $this->is_active,
            'variation_values' => $this->variation_values,
            'created_at' => $this->created_at,
        ];
    }
}
