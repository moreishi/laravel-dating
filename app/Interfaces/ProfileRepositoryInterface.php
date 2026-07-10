<?php

namespace App\Interfaces;

use App\Data\UpdateProfileData;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Models\User;

interface ProfileRepositoryInterface
{
    public function getAllExcept(int $userId, int $perPage = 12): LengthAwarePaginator;

    public function findById(int $id): ?User;

    public function findByIdOrFail(int $id): User;

    public function update(int $id, UpdateProfileData $data): User;
}
