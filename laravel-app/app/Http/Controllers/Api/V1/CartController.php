<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\AddCartItemRequest;
use App\Http\Requests\ApplyCartCouponRequest;
use App\Http\Requests\EstimateShippingRequest;
use App\Http\Requests\UpdateCartItemRequest;
use App\Http\Resources\CartItemResource;
use App\Http\Resources\CartResource;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class CartController
{
    public function __construct(private readonly CartService $cartService)
    {
    }

    /**
     * GET /api/v1/cart
     */
    public function index(Request $request): JsonResponse
    {
        $cart = $this->cartService->getCart($request->user()->id);

        return response()->json([
            'data' => new CartResource($cart),
        ]);
    }

    /**
     * POST /api/v1/cart/items
     */
    public function store(AddCartItemRequest $request): JsonResponse
    {
        try {
            $cartItem = $this->cartService->addItem(
                $request->user()->id,
                (int) $request->validated('product_id'),
                (int) $request->validated('quantity', 1),
                $request->validated('product_sku_id') !== null
                    ? (int) $request->validated('product_sku_id')
                    : null,
            );
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'message' => 'Item adicionado ao carrinho com sucesso.',
            'data'    => new CartItemResource($cartItem->load('product')),
        ], 201);
    }

    /**
     * PUT /api/v1/cart/items/{id}
     */
    public function update(UpdateCartItemRequest $request, int $id): JsonResponse
    {
        try {
            $cartItem = $this->cartService->updateQuantity(
                $request->user()->id,
                $id,
                (int) $request->validated('quantity'),
            );
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'message' => 'Quantidade atualizada com sucesso.',
            'data'    => new CartItemResource($cartItem->load('product')),
        ]);
    }

    /**
     * DELETE /api/v1/cart/items/{id}
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        try {
            $this->cartService->removeItem($request->user()->id, $id);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['message' => 'Item removido do carrinho.']);
    }

    /**
     * POST /api/v1/cart/clear
     */
    public function clear(Request $request): JsonResponse
    {
        $this->cartService->clearCart($request->user()->id);

        return response()->json(['message' => 'Carrinho limpo com sucesso.']);
    }

    /**
     * POST /api/v1/cart/coupon
     */
    public function applyCoupon(ApplyCartCouponRequest $request): JsonResponse
    {
        try {
            $cart = $this->cartService->applyCoupon(
                $request->user()->id,
                (string) $request->validated('coupon_code'),
            );
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'message' => 'Cupom aplicado com sucesso.',
            'data'    => new CartResource($cart),
        ]);
    }

    /**
     * POST /api/v1/cart/shipping
     */
    public function estimateShipping(EstimateShippingRequest $request): JsonResponse
    {
        try {
            $shippingCost = $this->cartService->estimateShippingCost(
                $request->user()->id,
                (string) $request->validated('postal_code'),
            );
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'data' => ['shipping_cost' => $shippingCost],
        ]);
    }
}
