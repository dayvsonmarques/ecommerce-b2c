<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Services\PaymentService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class ProcessPaymentWebhookJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public int $tries = 5;
    public int $backoff = 30;

    public function __construct(
        private readonly string $gatewayPaymentId,
        private readonly string $eventType,
        private readonly array  $payload,
    ) {
    }

    public function handle(PaymentService $paymentService): void
    {
        $paymentService->handleWebhook(
            $this->gatewayPaymentId,
            $this->eventType,
            $this->payload,
        );
    }

    public function failed(Throwable $exception): void
    {
        \Log::error('Payment webhook processing failed', [
            'payment_id' => $this->gatewayPaymentId,
            'event'      => $this->eventType,
            'error'      => $exception->getMessage(),
        ]);
    }
}
