<?php

namespace Mini\Cms\default\Controllers;

use Mini\Cms\Controller\ControllerInterface;
use Mini\Cms\Controller\Request;
use Mini\Cms\Controller\Response;

class Dashboard implements ControllerInterface
{

    public function __construct(Request &$request, Response &$response)
    {
    }

    /**
     * @inheritDoc
     */
    public function isAccessAllowed(): bool
    {
        // TODO: Implement isAccessAllowed() method.
        return true;
    }

    public function writeBody(): void
    {
        // TODO: Implement writeBody() method.
    }
}