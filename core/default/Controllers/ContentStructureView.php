<?php

namespace Mini\Cms\default\Controllers;

use Mini\Cms\Controller\ControllerInterface;
use Mini\Cms\Controller\Request;
use Mini\Cms\Controller\Response;

class ContentStructureView implements ControllerInterface
{

    public function __construct(Request &$request, Response &$response)
    {
    }

    public function isAccessAllowed(): bool
    {
        // TODO: Implement isAccessAllowed() method.
    }

    public function writeBody(): void
    {
        // TODO: Implement writeBody() method.
    }
}