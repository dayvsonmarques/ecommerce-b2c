<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\OrderStatus;
use App\Events\OrderCancelled;
use App\Events\OrderCreated;
use App\Events\OrderDelivered;
use App\Events\OrderPaid;
use App\Events\OrderShipped;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\UserAddress;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class OrderService
{
    public function __construct(private readonly StockService $stockService)
    {
    }

    // -------------------------------------------------------------------------
    // Create order from cart
    // -------------------------------------------------------------------------

    /**
     * Cria um pedido a partir do carrinho do usuário.
     *
     * @throws ValidationException|RuntimeException
     */
    public function createFromCart(int $userId, array $data): Order
    {
        $cart = Cart::with(['items.product', 'items.sku'])
            ->where('user_id', $userId)
            ->firstOrFail();

        if ($cart->items->isEmpty()) {
            throw new RuntimeException('O carrinho está vazio.');
        }

        // Validar endereço
        $address = UserAddress::where('id', $data['shipping_address_id'])
            ->where('user_id', $userId)
            ->firstOrFail();

        // Confirmar estoque e reservar
        foreach ($cart->items as $item) {
            $this->stockService->reserve($item->product_id, $item->product_sku_id, $item->quantity);
        }

        // Criar pedido
        $order = Order::create([
            'user_id'               => $userId,
            'number'                => Order::generateNumber(),
            'status'                => OrderStatus::Pending,
            'subtotal'              => $cart->subtotal,
            'shipping_cost'         => $cart->shipping_cost,
            'discount_amount'       => $cart->discount_amount,
            'total'                 => $cart->total,
            'coupon_code'           => $cart->coupon_code,
            'shipping_street'       => $address->street,
            'shipping_number'       => $address->number,
            'shipping_complement'   => $address->complement,
            'shipping_neighborhood' => $address->neighborhood,
            'shipping_city'         => $address->city,
            'shipping_state'        => $address->state,
            'shipping_postal_code'  => $address->postal_code,
            'notes'                 => $data['notes'] ?? null,
        ]);

        // Copiar itens do carrinho → order_items (snapshot)
        foreach ($cart->items as $item) {
            $order->items()->create([
                'product_id'       => $item->product_id,
                'product_sku_id'   => $item->product_sku_id,
                'product_name'     => $item->product->name,
                'product_sku'      => $item->sku?->sku ?? $item->product->sku,
                'quantity'         => $item->quantity,
                'unit_price'       => $item->unit_price,
                'total_price'      => $item->total_price,
                'variation_values' => $item->sku?->variation_values ?? null,
            ]);
        }

        // Incrementar uso do cupom
        if ($cart->coupon_code) {
            Coupon::where('code', $cart->coupon_code)->first()?->incrementUsage();
        }

        // Limpar carrinho
        $cart->items()->delete();
        $cart->update([
            'subtotal' => 0, 'items_count' => 0, 'shipping_cost' => 0,
            'coupon_code' => null, 'discount_amount' => 0, 'total' => 0,
        ]);

        // Registrar histórico inicial
        $order->statusHistories()->create([
            'from_status' => '',
            'to_status'   => OrderStatus::Pending->value,
            'changed_by'  => 'system',
            'comment'     => 'Pedido criado',
        ]);

        event(new OrderCreated($order));

        return $order->load(['items', 'statusHistories']);
    }

    // -------------------------------------------------------------------------
    // Queries
    // -------------------------------------------------------------------------

    public function listForUser(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return Order::where('user_id', $userId)
            ->with(['items'])
            ->latest()
            ->paginate($perPage);
    }

    public function findForUser(int $orderId, int $userId): Order
    {
        return Order::where('id', $orderId)
            ->where('user_id', $userId)
            ->with(['items', 'payment', 'statusHistories'])
            ->firstOrFail();
    }

    // -------------------------------------------------------------------------
    // Transitions (called by admin or webhooks)
    // -------------------------------------------------------------------------

    public function markAsPaid(Order $order): void
    {
        $order->transitionTo(OrderStatus::Paid, 'system', 'Pagamento confirmado');
        event(new OrderPaid($order));
    }

    public function markAsProcessing(Order $order): void
    {
        $order->transitionTo(OrderStatus::Processing, 'admin', 'Em processamento');
    }

    public function markAsShipped(Order $order, string $trackingCode, string $carrier): void
    {
        $order->tracking_code    = $trackingCode;
        $order->shipping_carrier = $carrier;
        $order->save();

        $order->transitionTo(OrderStatus::Shipped, 'admin', "Enviado via {$carrier}");
        event(new OrderShipped($order, $trackingCode, $carrier));
    }

    public function markAsDelivered(Order $order): void
    {
        $order->transitionTo(OrderStatus::Delivered, 'system', 'Entregue ao destinatário');
        event(new OrderDelivered($order));
    }

    public function cancel(Order $order, ?string $reason = null, ?string $changedBy = null): void
    {
        $order->transitionTo(OrderStatus::Cancelled, $changedBy ?? 'user', $reason);
        event(new OrderCancelled($order, $reason));
    }
}
