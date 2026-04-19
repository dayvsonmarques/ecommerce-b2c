<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('gateway');                         // stripe | pagarme
            $table->string('gateway_payment_id')->nullable();  // id do objeto no gateway
            $table->string('gateway_charge_id')->nullable();
            $table->enum('method', ['credit_card', 'pix', 'boleto']);
            $table->enum('status', ['pending', 'processing', 'paid', 'failed', 'refunded'])->default('pending');
            $table->decimal('amount', 10, 2);
            $table->integer('installments')->default(1);
            $table->string('boleto_url')->nullable();
            $table->string('boleto_barcode')->nullable();
            $table->string('pix_qr_code')->nullable();
            $table->string('pix_qr_code_text')->nullable();
            $table->timestamp('pix_expires_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->json('gateway_response')->nullable();       // payload bruto do gateway
            $table->timestamps();

            $table->index(['gateway', 'gateway_payment_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
