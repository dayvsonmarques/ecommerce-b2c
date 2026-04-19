<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\UserAddress;
use Illuminate\Database\Eloquent\Collection;

class AddressRepository
{
    public function find(int $id): ?UserAddress
    {
        return UserAddress::find($id);
    }

    public function findByUser(int $userId): Collection
    {
        return UserAddress::where('user_id', $userId)->get();
    }

    public function create(array $data): UserAddress
    {
        return UserAddress::create($data);
    }

    public function update(UserAddress $address, array $data): bool
    {
        return $address->update($data);
    }

    public function delete(UserAddress $address): bool
    {
        return $address->delete();
    }

    public function getDefault(int $userId): ?UserAddress
    {
        return UserAddress::where('user_id', $userId)
            ->where('is_default', true)
            ->first();
    }
}
