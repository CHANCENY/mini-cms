<?php

namespace Mini\Cms\Modules\Modal;

use Throwable;

class MissingRelationColumnException extends \Exception
{
    public function __construct(string $message = "", int $code = 448, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}