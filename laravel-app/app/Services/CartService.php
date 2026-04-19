<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\ProductSku;
use App\Repositories\CartRepository;
use RuntimeException;

class CartService
{
    public function __construct(private readonly CartRepository $cartRepository)
    {
    }

    // -------------------------------------------------------------------------
    // Items
    // -------------------------------------------------------------------------

    public function addItem(
        int  $userId,
        int  $productId,
        int  $quantity = 1,
        ?int $skuId = null,
    ): CartItem {
        /** @var Product $product */
        $product = Product::active()->findOrFail($productId);

        $availableStock = $skuId
            ? $this->resolveSkuStock($product, $skuId)
            : $product->quantity;

        if ($quantity > $availableStock) {
            throw new RuntimeException('Quantidade indisponível em estoque.');
        }

        $cart     = $this->cartRepository->findOrCreateByUserId($userId);
        $cartItem = $this->cartRepository->addItem($cart, $product, $quantity, $skuId);

        // Verificação pós-adição (acúmulo de quantidades existentes)
        if ($cartItem->quantity > $availableStock) {
            throw new RuntimeException('Quantidade total no carrinho excede o estoque disponível.');
        }

        $this->refreshCartTotals($cart);

        return $cartItem;
    }

    public function updateQuantity(int $userId, int $cartItemId, int $quantity): CartItem
    {
        $cart = $this->cartRepository->findOrCreateByUserId($userId);
        $item = $cart->items()->with('product')->findOrFail($cartItemId);

        $availableStock = $item->product->quantity;

        if ($quantity > $availableStock) {
            throw new RuntimeException('Quantidade indisponível em estoque.');
        }

        $item = $this->cartRepository->updateItemQuantity($item, $quantity);
        $this->refreshCartTotals($cart);

        return $item;
    }

    public function removeItem(int $userId, int $cartItemId): void
    {
        $cart = $this->cartRepository->findOrCreateByUserId($userId);
        $item = $cart->items()->findOrFail($cartItemId);

        $this->cartRepository->removeItem($item);
        $this->refreshCartTotals($cart);
    }

    public function getCart(int $userId): Cart
    {
        return $this->cartRepository->getCartWithItems($userId);
    }

    public function clearCart(int $userId): void
    {
        $cart = $this->cartRepository->findOrCreateByUserId($userId);
        $this->cartRepository->clearCart($cart);
    }

    // -------------------------------------------------------------------------
    // Coupon
    // -------------------------------------------------------------------------

    public function applyCoupon(int $userId, string $couponCode): Cart
    {
        $cart = $this->cartRepository->getCartWithItems($userId);

        $coupon = Coupon::where('code', strtoupper(trim($couponCode)))->first();

        if (! $coupon || ! $coupon->isValid()) {
            throw new RuntimeException('Cupom inválido ou expirado.');
        }

        if ((float) $cart->subtotal < (float) $coupon->min_order_value) {
            throw new RuntimeException(
                "Este cupom exige pedido mínimo de R$ {$coupon->min_order_value}."
            );
        }

        $cart->coupon_code     = $coupon->code;
        $cart->discount_amount = $coupon->calculateDiscount((float) $cart->subtotal);
        $cart->total           = $this->calculateTotal($cart);
        $cart->save();

        return $cart->refresh();
    }

    // -------------------------------------------------------------------------
    // Shipping
    // -------------------------------------------------------------------------

    public function estimateShippingCost(int $userId, string $postalCode): float
    {
        $cart = $this->cartRepository->getCartWithItems($userId);

        if ($cart->items->isEmpty()) {
            throw new RuntimeException('O carrinho está vazio.');
        }

        $shippingCost          = $this->calculateShippingRate($postalCode);
        $cart->shipping_cost   = $shippingCost;
        $cart->total           = $this->calculateTotal($cart);
        $cart->save();

        return $shippingCost;
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function refreshCartTotals(Cart $cart): void
    {
        $cart->load('items');
        $cart->subtotal    = $cart->items->sum(fn (CartItem $item) => $item->unit_price * $item->quantity);
        $cart->items_count = $cart->items->sum('quantity');

        // Recalcular desconto baseado no novo subtotal
        if ($cart->coupon_code) {
            $coupon = Coupon::where('code', $cart->coupon_code)->first();
            $cart->discount_amount = $coupon?->isValid()
                ? $coupon->calculateDiscount((float) $cart->subtotal)
                : 0.00;
        }

        $cart->total = $this->calculateTotal($cart);
        $cart->save();
    }

    private function calculateTotal(Cart $cart): float
    {
        $total = (float) $cart->subtotal
               + (float) $cart->shipping_cost
               - (float) $cart->discount_amount;

        return max(round($total, 2), 0.00);
    }

    private function calculateShippingRate(string $postalCode): float
    {
        $digits = preg_replace('/\D+/', '', $postalCode);
        $prefix = (int) substr($digits, 0, 3);

        return match (true) {
            $prefix <= 199 => 12.00,   // SP capital
            $prefix <= 299 => 14.00,   // SP interior
            $prefix <= 399 => 16.00,   // MG, RJ
            $prefix <= 599 => 18.00,   // Sul + Centro-Oeste
            default        => 22.00,   // Norte + Nordeste
        };
    }

    private function resolveSkuStock(Product $product, int $skuId): int
    {
        /** @var ProductSku|null $sku */
        $sku = $product->skus()->active()->find($skuId);

        if (! $sku) {
            throw new RuntimeException('Variação do produto não encontrada ou inativa.');
        }

        return $sku->quantity;
    }
}
