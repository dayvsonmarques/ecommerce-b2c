<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adiciona branch_id nas tabelas que precisam de isolamento por filial.
 *
 * Regra de negócio:
 *  - orders, carts         → sempre vinculados a uma filial (NOT NULL após migração)
 *  - product_skus          → estoque é por filial (NOT NULL)
 *  - coupons               → podem ser da loja toda ou de uma filial (nullable)
 */
return new class extends Migration
{
    protected $connection = 'tenant';

    public function up(): void
    {
        // Pedidos — cada pedido pertence a uma filial
        Schema::connection('tenant')->table('orders', function (Blueprint $table): void {
            $table->foreignId('branch_id')
                ->nullable()  // nullable para compatibilidade com dados existentes
                ->after('user_id')
                ->constrained('branches')
                ->nullOnDelete();

            $table->index('branch_id');
        });

        // Carrinhos — carrinho está associado à filial onde o cliente está comprando
        Schema::connection('tenant')->table('carts', function (Blueprint $table): void {
            $table->foreignId('branch_id')
                ->nullable()
                ->after('user_id')
                ->constrained('branches')
                ->nullOnDelete();

            $table->index('branch_id');
        });

        // SKUs — estoque é controlado por filial
        Schema::connection('tenant')->table('product_skus', function (Blueprint $table): void {
            $table->foreignId('branch_id')
                ->nullable()
                ->after('product_id')
                ->constrained('branches')
                ->nullOnDelete();

            $table->index('branch_id');
        });

        // Cupons — podem ser globais (null) ou específicos de uma filial
        Schema::connection('tenant')->table('coupons', function (Blueprint $table): void {
            $table->foreignId('branch_id')
                ->nullable()
                ->after('id')
                ->constrained('branches')
                ->nullOnDelete();

            $table->index('branch_id');
        });
    }

    public function down(): void
    {
        Schema::connection('tenant')->table('coupons', function (Blueprint $table): void {
            $table->dropForeign(['branch_id']);
            $table->dropColumn('branch_id');
        });

        Schema::connection('tenant')->table('product_skus', function (Blueprint $table): void {
            $table->dropForeign(['branch_id']);
            $table->dropColumn('branch_id');
        });

        Schema::connection('tenant')->table('carts', function (Blueprint $table): void {
            $table->dropForeign(['branch_id']);
            $table->dropColumn('branch_id');
        });

        Schema::connection('tenant')->table('orders', function (Blueprint $table): void {
            $table->dropForeign(['branch_id']);
            $table->dropColumn('branch_id');
        });
    }
};
