<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'number'           => $this->number,
            'status'           => $this->status->value,
            'status_label'     => $this->status->label(),
            'subtotal'         => $this->subtotal,
            'shipping_cost'    => $this->shipping_cost,
            'discount_amount'  => $this->discount_amount,
            'total'            => $this->total,
            'coupon_code'      => $this->coupon_code,
            'shipping_address' => $this->shipping_address,
            'tracking_code'    => $this->tracking_code,
            'shipping_carrier' => $this->shipping_carrier,
            'paid_at'          => $this->paid_at,
            'shipped_at'       => $this->shipped_at,
            'delivered_at'     => $this->delivered_at,
            'cancelled_at'     => $this->cancelled_at,
            'notes'            => $this->notes,
            'items'            => OrderItemResource::collection($this->whenLoaded('items')),
            'payment'          => new PaymentResource($this->whenLoaded('payment')),
            'timeline'         => $this->whenLoaded(
                'statusHistories',
                fn () => $this->statusHistories->map(fn ($h) => [
                    'from'       => $h->from_status,
                    'to'         => $h->to_status,
                    'changed_by' => $h->changed_by,
                    'comment'    => $h->comment,
                    'at'         => $h->created_at,
                ])
            ),
            'created_at'       => $this->created_at,
            'updated_at'       => $this->updated_at,
        ];
    }
}
