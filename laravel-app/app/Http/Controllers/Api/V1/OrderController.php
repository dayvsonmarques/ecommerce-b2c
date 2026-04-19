<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class OrderController
{
    public function __construct(private readonly OrderService $orderService)
    {
    }

    /**
     * GET /api/v1/orders
     */
    public function index(Request $request): JsonResponse
    {
        $orders = $this->orderService->listForUser(
            $request->user()->id,
            (int) $request->input('per_page', 15),
        );

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
     * POST /api/v1/orders
     */
    public function store(StoreOrderRequest $request): JsonResponse
    {
        try {
            $order = $this->orderService->createFromCart(
                $request->user()->id,
                $request->validated(),
            );
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'message' => 'Pedido criado com sucesso.',
            'data'    => new OrderResource($order),
        ], 201);
    }

    /**
     * GET /api/v1/orders/{order}
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $order = $this->orderService->findForUser($id, $request->user()->id);

        return response()->json([
            'data' => new OrderResource($order),
        ]);
    }

    /**
     * POST /api/v1/orders/{order}/cancel
     */
    public function cancel(Request $request, int $id): JsonResponse
    {
        $order = $this->orderService->findForUser($id, $request->user()->id);

        try {
            $this->orderService->cancel(
                $order,
                $request->input('reason'),
                $request->user()->name,
            );
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'message' => 'Pedido cancelado com sucesso.',
            'data'    => new OrderResource($order->refresh()),
        ]);
    }
}
