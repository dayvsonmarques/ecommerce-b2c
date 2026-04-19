<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController
{
    /**
     * GET /api/v1/products
     *
     * Query params: category_id, search, min_price, max_price,
     *               sort (name|price|created_at), direction (asc|desc), per_page
     */
    public function index(Request $request): JsonResponse
    {
        $query = Product::active()->with(['category', 'variations.values', 'skus']);

        if ($request->filled('category_id')) {
            $query->where('category_id', (int) $request->input('category_id'));
        }

        if ($request->filled('search')) {
            $query->search((string) $request->input('search'));
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', (float) $request->input('min_price'));
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', (float) $request->input('max_price'));
        }

        if ($request->boolean('in_stock')) {
            $query->inStock();
        }

        $allowedSorts = ['name', 'price', 'created_at'];
        $sort         = in_array($request->input('sort'), $allowedSorts, true)
            ? $request->input('sort')
            : 'created_at';
        $direction = $request->input('direction', 'desc') === 'asc' ? 'asc' : 'desc';

        $products = $query->orderBy($sort, $direction)
                          ->paginate((int) $request->input('per_page', 15));

        return response()->json([
            'data'       => ProductResource::collection($products->items()),
            'pagination' => [
                'total'        => $products->total(),
                'per_page'     => $products->perPage(),
                'current_page' => $products->currentPage(),
                'last_page'    => $products->lastPage(),
                'from'         => $products->firstItem(),
                'to'           => $products->lastItem(),
            ],
        ]);
    }

    /**
     * GET /api/v1/products/{product}
     */
    public function show(Product $product): JsonResponse
    {
        if (! $product->is_active) {
            return response()->json(['message' => 'Produto não encontrado.'], 404);
        }

        $product->load(['category', 'variations.values', 'skus', 'prices']);

        return response()->json([
            'data' => new ProductResource($product),
        ]);
    }

    /**
     * GET /api/v1/products/{product}/related
     *
     * Retorna até 8 produtos da mesma categoria, excluindo o atual.
     */
    public function related(Product $product): JsonResponse
    {
        if (! $product->is_active) {
            return response()->json(['message' => 'Produto não encontrado.'], 404);
        }

        $related = Product::active()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->with(['category', 'skus'])
            ->limit(8)
            ->get();

        return response()->json([
            'data' => ProductResource::collection($related),
        ]);
    }
}
