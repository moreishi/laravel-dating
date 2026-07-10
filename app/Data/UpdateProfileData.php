<?php

namespace App\Data;

use App\Enums\GenderEnum;
use Spatie\LaravelData\Data;

class UpdateProfileData extends Data
{
    public function __construct(
        public readonly string $name,
        public readonly int $age,
        public readonly ?string $bio,
        public readonly ?GenderEnum $gender,
    ) {}

    public static function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'age' => ['required', 'integer', 'min:18', 'max:120'],
            'bio' => ['nullable', 'string', 'max:5000'],
            'gender' => ['nullable', 'string', 'in:male,female,other'],
        ];
    }
}
