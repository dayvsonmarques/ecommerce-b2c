<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'central';

    public function up(): void
    {
        Schema::connection('central')->create('tenants', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();          // identificador URL-safe
            $table->string('database')->unique();      // nome do banco MySQL do tenant
            $table->string('plan')->default('basic');  // basic | pro | enterprise
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable();      // configurações livres por tenant
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('central')->dropIfExists('tenants');
    }
};
