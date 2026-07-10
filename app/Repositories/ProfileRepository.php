<?php

namespace App\Repositories;

use App\Data\UpdateProfileData;
use App\Interfaces\ProfileRepositoryInterface;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProfileRepository implements ProfileRepositoryInterface
{
    public function getAllExcept(int $userId, int $perPage = 12): LengthAwarePaginator
    {
        return User::where('id', '!=', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function findById(int $id): ?User
    {
        return User::find($id);
    }

    public function findByIdOrFail(int $id): User
    {
        return User::findOrFail($id);
    }

    public function update(int $id, UpdateProfileData $data): User
    {
        $user = $this->findByIdOrFail($id);

        $user->update([
            'name' => $data->name,
            'age' => $data->age,
            'bio' => $data->bio,
            'gender' => $data->gender?->value,
        ]);

        return $user->fresh();
    }
}
