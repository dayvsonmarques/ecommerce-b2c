<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\PaymentResource;
use App\Jobs\ProcessPaymentWebhookJob;
use App\Models\Order;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class PaymentController
{
    public function __construct(private readonly PaymentService $paymentService)
    {
    }

    /**
     * POST /api/v1/orders/{order}/pay
     *
     * Inicia o pagamento de um pedido.
     */
    public function pay(Request $request, int $orderId): JsonResponse
    {
        $validated = $request->validate([
            'method'       => ['required', 'in:credit_card,pix,boleto'],
            'card_token'   => ['required_if:method,credit_card', 'nullable', 'string'],
            'installments' => ['nullable', 'integer', 'min:1', 'max:12'],
        ]);

        $order = Order::where('id', $orderId)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        try {
            $payment = $this->paymentService->initiate($order, $validated);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'message' => 'Pagamento iniciado.',
            'data'    => new PaymentResource($payment),
        ], 201);
    }

    /**
     * POST /api/v1/webhooks/payment
     *
     * Recebe eventos do gateway (Stripe / Pagar.me).
     * Rota pública — valide a assinatura antes de processar.
     */
    public function webhook(Request $request): JsonResponse
    {
        $payload = $request->all();

        // Stripe envia o tipo do evento em `type`; Pagar.me em `event`
        $eventType        = $payload['type'] ?? $payload['event'] ?? 'unknown';
        $gatewayPaymentId = $payload['data']['object']['id']           // Stripe
                         ?? $payload['transaction']['id']              // Pagar.me
                         ?? null;

        if ($gatewayPaymentId === null) {
            return response()->json(['message' => 'Payload inválido.'], 400);
        }

        ProcessPaymentWebhookJob::dispatch($gatewayPaymentId, $eventType, $payload)
            ->onQueue('webhooks');

        return response()->json(['message' => 'Webhook recebido.']);
    }
}
