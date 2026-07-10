<?php

namespace App\Exceptions;

use Exception;

class CannotMessageSelfException extends Exception
{
    public function __construct()
    {
        parent::__construct('You cannot start a conversation with yourself.');
    }
}
