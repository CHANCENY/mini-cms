<?php

namespace Mini\Modules\custom\simple_module\src\Controllers;

use Mini\Cms\Controller\ControllerInterface;
use Mini\Cms\Controller\Request;
use Mini\Cms\Controller\Response;

class SimpleController implements ControllerInterface
{

    public function __construct(Request &$request, private Response &$response)
    {
    }

    /**
     * @inheritDoc
     */
    public function isAccessAllowed(): bool
    {
        return true;
    }

    public function writeBody(): void
    {
        $this->response->write("Hello world!");
    }

    public function simple()
    {
        $this->response->write("Hello world! 2");
    }
}