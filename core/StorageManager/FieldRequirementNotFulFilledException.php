<?php

namespace Mini\Cms\StorageManager;

use Throwable;

class FieldRequirementNotFulFilledException extends \Exception
{

    public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        $message = $message . " is missing value";
        parent::__construct($message, $code, $previous);
    }
}