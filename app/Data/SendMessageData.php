<?php

namespace App\Data;

use Spatie\LaravelData\Data;

class SendMessageData extends Data
{
    public function __construct(
        public readonly int $conversationId,
        public readonly string $content,
    ) {}

    public static function rules(): array
    {
        return [
            'content' => ['required', 'string', 'max:2000'],
        ];
    }
}
