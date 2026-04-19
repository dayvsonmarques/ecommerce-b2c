<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;

class CartRepository
{
    public function findOrCreateByUserId(int $userId): Cart
    {
        return Cart::firstOrCreate(['user_id' => $userId]);
    }

    public function getCartWithItems(int $userId): Cart
    {
        return Cart::with(['items.product', 'items.sku'])
            ->firstOrCreate(['user_id' => $userId]);
    }

    public function addItem(Cart $cart, Product $product, int $quantity, ?int $skuId = null): CartItem
    {
        $cartItem = $cart->items()->firstOrNew([
            'product_id'     => $product->id,
            'product_sku_id' => $skuId,
        ]);

        $unitPrice            = $skuId
            ? ((float) ($product->skus()->find($skuId)?->price ?? $product->price))
            : (float) $product->price;

        $cartItem->quantity    = $cartItem->exists ? $cartItem->quantity + $quantity : $quantity;
        $cartItem->unit_price  = $unitPrice;
        $cartItem->total_price = round($unitPrice * $cartItem->quantity, 2);
        $cartItem->save();

        return $cartItem;
    }

    public function updateItemQuantity(CartItem $item, int $quantity): CartItem
    {
        $item->quantity    = $quantity;
        $item->total_price = round((float) $item->unit_price * $quantity, 2);
        $item->save();

        return $item;
    }

    public function removeItem(CartItem $item): void
    {
        $item->delete();
    }

    public function clearCart(Cart $cart): void
    {
        $cart->items()->delete();
        $cart->update([
            'subtotal'        => 0.00,
            'items_count'     => 0,
            'shipping_cost'   => 0.00,
            'coupon_code'     => null,
            'discount_amount' => 0.00,
            'total'           => 0.00,
        ]);
    }
}
