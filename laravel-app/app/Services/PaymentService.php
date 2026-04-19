<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use RuntimeException;

class PaymentService
{
    /**
     * Inicia o pagamento de um pedido.
     *
     * @param  array{method: string, card_token?: string|null, installments?: int}  $data
     */
    public function initiate(Order $order, array $data): Payment
    {
        if ($order->payment()->exists()) {
            throw new RuntimeException('Este pedido já possui um pagamento.');
        }

        $gateway = $this->resolveGateway();

        $payment = match ($data['method']) {
            'credit_card' => $this->processCreditCard($order, $gateway, $data),
            'pix'         => $this->processPix($order, $gateway),
            'boleto'      => $this->processBoleto($order, $gateway),
            default       => throw new RuntimeException("Método de pagamento '{$data['method']}' não suportado."),
        };

        return $payment;
    }

    /**
     * Processa o webhook do gateway e atualiza o estado do pagamento + pedido.
     */
    public function handleWebhook(string $gatewayPaymentId, string $eventType, array $payload): void
    {
        $payment = Payment::where('gateway_payment_id', $gatewayPaymentId)->firstOrFail();
        $order   = $payment->order;

        $payment->gateway_response = $payload;

        match ($eventType) {
            'payment.succeeded', 'charge.succeeded', 'payment_intent.succeeded' => (function () use ($payment, $order) {
                $payment->status  = 'paid';
                $payment->paid_at = now();
                $payment->save();

                app(OrderService::class)->markAsPaid($order);
            })(),

            'payment.failed', 'charge.failed' => (function () use ($payment) {
                $payment->status = 'failed';
                $payment->save();
            })(),

            'refund.created', 'charge.refunded' => (function () use ($payment) {
                $payment->status = 'refunded';
                $payment->save();
            })(),

            default => $payment->save(),
        };
    }

    // -------------------------------------------------------------------------
    // Private: gateway methods
    // -------------------------------------------------------------------------

    private function resolveGateway(): string
    {
        return config('services.payment.gateway', 'stripe');
    }

    private function processCreditCard(Order $order, string $gateway, array $data): Payment
    {
        // Em produção: integrar com Stripe ou Pagar.me via SDK.
        // Aqui registramos o pagamento em estado "processing".
        $installments = (int) ($data['installments'] ?? 1);

        return Payment::create([
            'order_id'            => $order->id,
            'gateway'             => $gateway,
            'gateway_payment_id'  => 'pi_' . uniqid(),   // placeholder até webhook confirmar
            'method'              => 'credit_card',
            'status'              => 'processing',
            'amount'              => $order->total,
            'installments'        => $installments,
            'gateway_response'    => ['card_token' => $data['card_token'] ?? null],
        ]);
    }

    private function processPix(Order $order, string $gateway): Payment
    {
        $expiresAt = now()->addMinutes(30);

        // Em produção: gerar QR Code via gateway.
        $qrCode     = base64_encode("pix:{$order->number}:{$order->total}");
        $qrCodeText = "00020126580014BR.GOV.BCB.PIX0136{$order->number}5204000053039865802BR5999ECOMMERCE6009SAO PAULO62070503***6304";

        return Payment::create([
            'order_id'          => $order->id,
            'gateway'           => $gateway,
            'gateway_payment_id'=> 'pix_' . uniqid(),
            'method'            => 'pix',
            'status'            => 'pending',
            'amount'            => $order->total,
            'pix_qr_code'       => $qrCode,
            'pix_qr_code_text'  => $qrCodeText,
            'pix_expires_at'    => $expiresAt,
        ]);
    }

    private function processBoleto(Order $order, string $gateway): Payment
    {
        // Em produção: gerar boleto via gateway.
        return Payment::create([
            'order_id'          => $order->id,
            'gateway'           => $gateway,
            'gateway_payment_id'=> 'blt_' . uniqid(),
            'method'            => 'boleto',
            'status'            => 'pending',
            'amount'            => $order->total,
            'boleto_url'        => 'https://boleto.example.com/' . $order->number,
            'boleto_barcode'    => '1234.56789 0001.234567 89012.345678 9 00010000' . (int) ($order->total * 100),
        ]);
    }
}
