<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('number')->unique();              // e.g. ORD-20260412-00001
            $table->string('status')->default('pending');   // OrderStatus enum

            // Valores
            $table->decimal('subtotal', 10, 2);
            $table->decimal('shipping_cost', 10, 2)->default(0.00);
            $table->decimal('discount_amount', 10, 2)->default(0.00);
            $table->decimal('total', 10, 2);

            // Cupom
            $table->string('coupon_code')->nullable();

            // Endereço de entrega (snapshot no momento da compra)
            $table->string('shipping_street')->nullable();
            $table->string('shipping_number')->nullable();
            $table->string('shipping_complement')->nullable();
            $table->string('shipping_neighborhood')->nullable();
            $table->string('shipping_city')->nullable();
            $table->string('shipping_state', 2)->nullable();
            $table->string('shipping_postal_code')->nullable();

            // Rastreamento
            $table->string('tracking_code')->nullable();
            $table->string('shipping_carrier')->nullable();

            // Timestamps de transição
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('number');
        });

        Schema::create('order_status_histories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('from_status');
            $table->string('to_status');
            $table->string('changed_by')->nullable();   // user name or 'system'
            $table->text('comment')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_status_histories');
        Schema::dropIfExists('orders');
    }
};
