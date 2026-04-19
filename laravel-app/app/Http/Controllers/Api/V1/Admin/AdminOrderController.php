<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\OrderStatus;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class AdminOrderController
{
    public function __construct(private readonly OrderService $orderService)
    {
    }

    /**
     * GET /api/v1/admin/orders
     */
    public function index(Request $request): JsonResponse
    {
        $query = Order::with(['user', 'items', 'payment'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('user_id'), fn ($q) => $q->where('user_id', $request->input('user_id')))
            ->when($request->filled('search'), fn ($q) => $q->where('number', 'like', "%{$request->input('search')}%"))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('created_at', '>=', $request->input('from')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('created_at', '<=', $request->input('to')));

        $orders = $query->latest()->paginate((int) $request->input('per_page', 20));

        return response()->json([
            'data'       => OrderResource::collection($orders->items()),
            'pagination' => [
                'total'        => $orders->total(),
                'per_page'     => $orders->perPage(),
                'current_page' => $orders->currentPage(),
                'last_page'    => $orders->lastPage(),
            ],
        ]);
    }

    /**
     * GET /api/v1/admin/orders/{order}
     */
    public function show(Order $order): JsonResponse
    {
        $order->load(['user', 'items.product', 'payment', 'statusHistories']);

        return response()->json(['data' => new OrderResource($order)]);
    }

    /**
     * PATCH /api/v1/admin/orders/{order}/status
     */
    public function updateStatus(Request $request, Order $order): JsonResponse
    {
        $request->validate([
            'status'        => ['required', 'string'],
            'tracking_code' => ['nullable', 'string'],
            'carrier'       => ['nullable', 'string'],
            'comment'       => ['nullable', 'string', 'max:500'],
        ]);

        $newStatus = OrderStatus::from($request->input('status'));

        try {
            match ($newStatus) {
                OrderStatus::Processing => $this->orderService->markAsProcessing($order),
                OrderStatus::Shipped    => $this->orderService->markAsShipped(
                    $order,
                    $request->input('tracking_code', ''),
                    $request->input('carrier', 'Correios'),
                ),
                OrderStatus::Delivered  => $this->orderService->markAsDelivered($order),
                OrderStatus::Cancelled  => $this->orderService->cancel($order, $request->input('comment'), 'admin'),
                default                 => throw new RuntimeException("Transição para '{$newStatus->value}' não suportada por esta rota."),
            };
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'message' => "Status atualizado para '{$newStatus->label()}'.",
            'data'    => new OrderResource($order->refresh()->load('statusHistories')),
        ]);
    }

    /**
     * GET /api/v1/admin/reports/summary
     */
    public function reportSummary(Request $request): JsonResponse
    {
        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to   = $request->input('to', now()->toDateString());

        $orders = Order::whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59']);

        return response()->json([
            'data' => [
                'period'              => ['from' => $from, 'to' => $to],
                'total_orders'        => (clone $orders)->count(),
                'total_revenue'       => (clone $orders)->where('status', '!=', 'cancelled')->sum('total'),
                'orders_by_status'    => (clone $orders)->selectRaw('status, count(*) as count')
                    ->groupBy('status')
                    ->pluck('count', 'status'),
                'average_order_value' => (clone $orders)->where('status', '!=', 'cancelled')->avg('total'),
            ],
        ]);
    }
}
