<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminUserController
{
    /**
     * GET /api/v1/admin/users
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::query()
            ->when($request->filled('search'), fn ($q) => $q->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->input('search')}%")
                  ->orWhere('email', 'like', "%{$request->input('search')}%")
                  ->orWhere('cpf', 'like', "%{$request->input('search')}%");
            }))
            ->when($request->filled('is_active'), fn ($q) => $q->where('is_active', $request->boolean('is_active')));

        $users = $query->latest()->paginate((int) $request->input('per_page', 20));

        return response()->json([
            'data'       => UserResource::collection($users->items()),
            'pagination' => [
                'total'        => $users->total(),
                'per_page'     => $users->perPage(),
                'current_page' => $users->currentPage(),
                'last_page'    => $users->lastPage(),
            ],
        ]);
    }

    /**
     * GET /api/v1/admin/users/{user}
     */
    public function show(User $user): JsonResponse
    {
        $user->load('addresses');

        return response()->json(['data' => new UserResource($user)]);
    }

    /**
     * PATCH /api/v1/admin/users/{user}/toggle-active
     */
    public function toggleActive(User $user): JsonResponse
    {
        $user->update(['is_active' => ! $user->is_active]);
        $action = $user->is_active ? 'ativado' : 'desativado';

        return response()->json([
            'message' => "Usuário {$action} com sucesso.",
            'data'    => new UserResource($user),
        ]);
    }
}
