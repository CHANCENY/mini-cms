<?php

namespace Mini\Modules\contrib\Sample\src\Controller;

use Mini\Cms\Controller\ControllerInterface;
use Mini\Cms\Controller\Request;
use Mini\Cms\Controller\Response;

class Sample implements ControllerInterface
{

    public function __construct(protected Request &$request, protected Response &$response)
    {
    }

    public function isAccessAllowed(): bool
    {
       return true;
    }

    public function writeBody(): void
    {
        $this->response->write("<h2>Sample Controller</h2>");
    }
}