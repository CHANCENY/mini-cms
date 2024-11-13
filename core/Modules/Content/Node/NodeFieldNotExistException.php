<?php

namespace Mini\Cms\Modules\Content\Node;

use Throwable;

class NodeFieldNotExistException extends \Exception
{
    public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}