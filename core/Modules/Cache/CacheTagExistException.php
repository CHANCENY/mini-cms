<?php

namespace Mini\Cms\Modules\Cache;

use Throwable;

class CacheTagExistException extends \Exception
{
    public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}