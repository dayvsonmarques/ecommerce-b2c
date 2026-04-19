<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\StoreAddressRequest;
use App\Http\Requests\UpdateAddressRequest;
use App\Http\Resources\UserAddressResource;
use App\Repositories\AddressRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AddressController
{
    public function __construct(private AddressRepository $addressRepository)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $addresses = $this->addressRepository->findByUser($request->user()->id);

        return response()->json([
            'data' => UserAddressResource::collection($addresses),
        ], 200);
    }

    public function store(StoreAddressRequest $request): JsonResponse
    {
        $address = $this->addressRepository->create([
            'user_id' => $request->user()->id,
            ...$request->validated(),
        ]);

        return response()->json([
            'message' => 'Endereço criado com sucesso.',
            'data' => new UserAddressResource($address),
        ], 201);
    }

    public function update(UpdateAddressRequest $request, int $id): JsonResponse
    {
        $address = $this->addressRepository->find($id);

        if (!$address || $address->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Endereço não encontrado.',
            ], 404);
        }

        $this->addressRepository->update($address, $request->validated());

        return response()->json([
            'message' => 'Endereço atualizado com sucesso.',
            'data' => new UserAddressResource($address),
        ], 200);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $address = $this->addressRepository->find($id);

        if (!$address || $address->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Endereço não encontrado.',
            ], 404);
        }

        $this->addressRepository->delete($address);

        return response()->json([
            'message' => 'Endereço deletado com sucesso.',
        ], 200);
    }
}
