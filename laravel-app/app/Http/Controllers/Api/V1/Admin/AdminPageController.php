<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\PageResource;
use App\Models\Page;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminPageController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $pages = Page::orderBy('section')->orderBy('sort_order')->orderBy('title')->get();

        return PageResource::collection($pages);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title'      => ['required', 'string', 'max:255'],
            'slug'       => ['nullable', 'string', 'max:255', 'unique:pages,slug'],
            'content'    => ['nullable', 'string'],
            'section'    => ['required', Rule::in(['quick_links', 'support', 'custom'])],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active'  => ['boolean'],
        ]);

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['title']);
        }

        $page = Page::create($data);

        return response()->json([
            'message' => 'Página criada com sucesso.',
            'page'    => new PageResource($page),
        ], 201);
    }

    public function show(Page $page): PageResource
    {
        return new PageResource($page);
    }

    public function update(Request $request, Page $page): JsonResponse
    {
        $data = $request->validate([
            'title'      => ['sometimes', 'string', 'max:255'],
            'slug'       => ['sometimes', 'string', 'max:255', Rule::unique('pages', 'slug')->ignore($page->id)],
            'content'    => ['nullable', 'string'],
            'section'    => ['sometimes', Rule::in(['quick_links', 'support', 'custom'])],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active'  => ['boolean'],
        ]);

        $page->update($data);

        return response()->json([
            'message' => 'Página atualizada com sucesso.',
            'page'    => new PageResource($page),
        ]);
    }

    public function destroy(Page $page): JsonResponse
    {
        $page->delete();

        return response()->json(['message' => 'Página removida com sucesso.']);
    }
}
