<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('store_name')->default('Minha Loja');
            $table->text('store_description')->nullable();
            $table->string('logo_url')->nullable();

            // Endereço de origem (referência para cálculo de frete)
            $table->string('address_street')->nullable();
            $table->string('address_number', 20)->nullable();
            $table->string('address_complement')->nullable();
            $table->string('address_neighborhood')->nullable();
            $table->string('address_city')->nullable();
            $table->char('address_state', 2)->nullable();
            $table->string('address_postal_code', 9)->nullable();

            // Cobertura de envio
            $table->enum('shipping_coverage', ['state', 'national', 'international'])->default('national');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_settings');
    }
};
