<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PageResource;
use App\Models\Page;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PageController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $pages = Page::active()
            ->when($request->query('section'), fn($q, $s) => $q->section($s))
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();

        return PageResource::collection($pages);
    }

    public function show(string $slug): PageResource|JsonResponse
    {
        $page = Page::active()->where('slug', $slug)->first();

        if (! $page) {
            return response()->json(['message' => 'Página não encontrada.'], 404);
        }

        return new PageResource($page);
    }
}
