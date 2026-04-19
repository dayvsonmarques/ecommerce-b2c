<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Requests\ProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminProductController
{
    public function __construct(private readonly ProductService $productService)
    {
    }

    /**
     * GET /api/v1/admin/products
     */
    public function index(Request $request): JsonResponse
    {
        $query = Product::with(['category', 'skus'])
            ->when($request->filled('search'), fn ($q) => $q->search($request->input('search')))
            ->when($request->filled('category_id'), fn ($q) => $q->where('category_id', $request->input('category_id')))
            ->when($request->filled('is_active'), fn ($q) => $q->where('is_active', $request->boolean('is_active')))
            ->when($request->boolean('low_stock'), fn ($q) => $q->whereColumn('quantity', '<=', 'min_quantity_alert'));

        $products = $query->latest()->paginate((int) $request->input('per_page', 20));

        return response()->json([
            'data'       => ProductResource::collection($products->items()),
            'pagination' => [
                'total'        => $products->total(),
                'per_page'     => $products->perPage(),
                'current_page' => $products->currentPage(),
                'last_page'    => $products->lastPage(),
            ],
        ]);
    }

    /**
     * POST /api/v1/admin/products
     */
    public function store(ProductRequest $request): JsonResponse
    {
        $product = $this->productService->create($request->validated());

        return response()->json([
            'message' => 'Produto criado com sucesso.',
            'data'    => new ProductResource($product),
        ], 201);
    }

    /**
     * GET /api/v1/admin/products/{product}
     */
    public function show(Product $product): JsonResponse
    {
        $product->load(['category', 'variations.values', 'skus', 'prices']);

        return response()->json([
            'data' => new ProductResource($product),
        ]);
    }

    /**
     * PUT /api/v1/admin/products/{product}
     */
    public function update(ProductRequest $request, Product $product): JsonResponse
    {
        $product = $this->productService->update($product, $request->validated());

        return response()->json([
            'message' => 'Produto atualizado com sucesso.',
            'data'    => new ProductResource($product),
        ]);
    }

    /**
     * DELETE /api/v1/admin/products/{product}
     */
    public function destroy(Product $product): JsonResponse
    {
        $this->productService->delete($product);

        return response()->json(['message' => 'Produto removido com sucesso.']);
    }

    /**
     * PATCH /api/v1/admin/products/{product}/stock
     */
    public function adjustStock(Request $request, Product $product): JsonResponse
    {
        $request->validate([
            'delta'          => ['required', 'integer'],
            'product_sku_id' => ['nullable', 'integer', 'exists:product_skus,id'],
        ]);

        app(\App\Services\StockService::class)->adjust(
            $product->id,
            $request->input('product_sku_id'),
            (int) $request->input('delta'),
        );

        return response()->json(['message' => 'Estoque ajustado com sucesso.']);
    }
}
