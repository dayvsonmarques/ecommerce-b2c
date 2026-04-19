<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'method'           => $this->method,
            'status'           => $this->status,
            'amount'           => $this->amount,
            'installments'     => $this->installments,
            // PIX
            'pix_qr_code'      => $this->pix_qr_code,
            'pix_qr_code_text' => $this->pix_qr_code_text,
            'pix_expires_at'   => $this->pix_expires_at,
            // Boleto
            'boleto_url'       => $this->boleto_url,
            'boleto_barcode'   => $this->boleto_barcode,
            'paid_at'          => $this->paid_at,
            'created_at'       => $this->created_at,
        ];
    }
}
