<?php

namespace App\Data;

use Spatie\LaravelData\Data;

class StartConversationData extends Data
{
    public function __construct(
        public readonly int $recipientId,
    ) {}

    public static function rules(): array
    {
        return [
            'recipient_id' => ['required', 'integer', 'exists:users,id'],
        ];
    }
}
