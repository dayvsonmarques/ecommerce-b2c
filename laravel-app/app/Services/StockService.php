<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Product;
use App\Models\ProductSku;
use App\Notifications\LowStockNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use RuntimeException;

class StockService
{
    /**
     * Reserva estoque no momento do checkout (decremento real).
     *
     * @throws RuntimeException se não houver estoque suficiente
     */
    public function reserve(int $productId, ?int $skuId, int $quantity): void
    {
        DB::transaction(function () use ($productId, $skuId, $quantity) {
            if ($skuId) {
                $sku = ProductSku::lockForUpdate()->findOrFail($skuId);
                if ($sku->quantity < $quantity) {
                    throw new RuntimeException('Estoque insuficiente para o SKU selecionado.');
                }
                $sku->decrement('quantity', $quantity);
            } else {
                $product = Product::lockForUpdate()->findOrFail($productId);
                if ($product->quantity < $quantity) {
                    throw new RuntimeException("Estoque insuficiente para o produto #{$productId}.");
                }
                $product->decrement('quantity', $quantity);
                $this->checkLowStockAlert($product->fresh());
            }
        });
    }

    /**
     * Restaura estoque quando pedido é cancelado.
     */
    public function restore(int $productId, ?int $skuId, int $quantity): void
    {
        DB::transaction(function () use ($productId, $skuId, $quantity) {
            if ($skuId) {
                ProductSku::lockForUpdate()->findOrFail($skuId)->increment('quantity', $quantity);
            } else {
                Product::lockForUpdate()->findOrFail($productId)->increment('quantity', $quantity);
            }
        });
    }

    /**
     * Ajuste manual de estoque (admin).
     */
    public function adjust(int $productId, ?int $skuId, int $delta): void
    {
        DB::transaction(function () use ($productId, $skuId, $delta) {
            if ($skuId) {
                $sku = ProductSku::lockForUpdate()->findOrFail($skuId);
                $newQty = $sku->quantity + $delta;
                if ($newQty < 0) {
                    throw new RuntimeException('O ajuste resultaria em estoque negativo.');
                }
                $sku->update(['quantity' => $newQty]);
            } else {
                $product = Product::lockForUpdate()->findOrFail($productId);
                $newQty  = $product->quantity + $delta;
                if ($newQty < 0) {
                    throw new RuntimeException('O ajuste resultaria em estoque negativo.');
                }
                $product->update(['quantity' => $newQty]);
                $this->checkLowStockAlert($product->fresh());
            }
        });
    }

    // -------------------------------------------------------------------------
    // Private
    // -------------------------------------------------------------------------

    private function checkLowStockAlert(Product $product): void
    {
        if ($product->isLowStock()) {
            // Notifica admins (usuários com is_admin = true)
            $admins = \App\Models\User::where('is_active', true)
                ->whereNotNull('email') // placeholder: em produção filtrar por role
                ->limit(5)
                ->get();

            if ($admins->isNotEmpty()) {
                Notification::send($admins, new LowStockNotification($product));
            }
        }
    }
}
