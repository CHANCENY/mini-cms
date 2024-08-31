<?php

namespace Mini\Cms\Modules\Authentication;

use Throwable;

class AuthenticationMethodNotExistException extends \Exception
{
    public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}