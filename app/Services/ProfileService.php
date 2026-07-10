<?php

namespace App\Services;

use App\Data\UpdateProfileData;
use App\Interfaces\ProfileRepositoryInterface;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProfileService
{
    public function __construct(
        private readonly ProfileRepositoryInterface $profileRepository,
    ) {}

    public function getBrowseProfiles(int $userId): LengthAwarePaginator
    {
        return $this->profileRepository->getAllExcept($userId);
    }

    public function getProfile(int $id): User
    {
        return $this->profileRepository->findByIdOrFail($id);
    }

    public function updateProfile(int $id, UpdateProfileData $data): User
    {
        return $this->profileRepository->update($id, $data);
    }
}
