<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'tenant';

    public function up(): void
    {
        Schema::connection('tenant')->create('branches', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();  // código interno: SP01, RJ02
            $table->string('phone')->nullable();
            $table->string('email')->nullable();

            // Endereço
            $table->string('street')->nullable();
            $table->string('number', 20)->nullable();
            $table->string('complement')->nullable();
            $table->string('neighborhood')->nullable();
            $table->string('city')->nullable();
            $table->string('state', 2)->nullable();
            $table->string('postal_code', 9)->nullable();

            $table->boolean('is_active')->default(true);
            $table->boolean('is_headquarters')->default(false); // matriz

            $table->json('settings')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('tenant')->dropIfExists('branches');
    }
};
