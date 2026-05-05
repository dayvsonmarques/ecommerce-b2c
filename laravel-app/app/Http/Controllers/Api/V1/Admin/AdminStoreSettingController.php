<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Models\StoreSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminStoreSettingController
{
    public function show(): JsonResponse
    {
        return response()->json(['data' => StoreSetting::instance()]);
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'store_name'          => 'sometimes|string|max:255',
            'store_description'   => 'sometimes|nullable|string|max:1000',
            'logo_url'            => 'sometimes|nullable|url|max:500',
            'address_street'      => 'sometimes|nullable|string|max:255',
            'address_number'      => 'sometimes|nullable|string|max:20',
            'address_complement'  => 'sometimes|nullable|string|max:100',
            'address_neighborhood'=> 'sometimes|nullable|string|max:100',
            'address_city'        => 'sometimes|nullable|string|max:100',
            'address_state'       => 'sometimes|nullable|string|size:2',
            'address_postal_code' => 'sometimes|nullable|string|max:9',
            'shipping_coverage'   => 'sometimes|in:state,national,international',
        ]);

        $settings = StoreSetting::instance();
        $settings->update($validated);

        return response()->json([
            'message' => 'Configurações salvas com sucesso.',
            'data'    => $settings->fresh(),
        ]);
    }
}
