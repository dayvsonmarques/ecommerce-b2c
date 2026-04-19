<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'subtotal' => $this->subtotal,
            'shipping_cost' => $this->shipping_cost,
            'discount_amount' => $this->discount_amount,
            'coupon_code' => $this->coupon_code,
            'total' => $this->total,
            'items_count' => $this->items_count,
            'items' => CartItemResource::collection($this->whenLoaded('items')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
