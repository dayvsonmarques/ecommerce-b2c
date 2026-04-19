<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;

class CategoryController
{
    public function index(): JsonResponse
    {
        $categories = Category::active()
            ->roots()
            ->with('children')
            ->get();

        return response()->json([
            'data' => CategoryResource::collection($categories),
        ], 200);
    }

    public function show(Category $category): JsonResponse
    {
        if (!$category->is_active) {
            return response()->json([
                'message' => 'Categoria não encontrada.',
            ], 404);
        }

        $category->load('children', 'products');

        return response()->json([
            'data' => new CategoryResource($category),
        ], 200);
    }
}
