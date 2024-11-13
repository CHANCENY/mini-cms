<?php

namespace Mini\Cms\App;

use Mini\Cms\Controller\AccessDeniedRouteException;
use Mini\Cms\Controller\BadGateWayException;
use Mini\Cms\Controller\ControllerHandlerNotFoundException;
use Mini\Cms\Controller\ControllerMissingException;
use Mini\Cms\Controller\PageNotFoundException;
use Mini\Cms\Controller\Route;
use Mini\Cms\Controller\TemporaryUnAvailableException;

readonly class App
{
    /**
     * @throws PageNotFoundException
     * @throws TemporaryUnAvailableException
     * @throws AccessDeniedRouteException
     * @throws ControllerMissingException
     * @throws ControllerHandlerNotFoundException
     * @throws BadGateWayException
     */
    public function __construct(private string $method, private string $path, private Route $route)
    {
        $this->route->match($this->method, $this->path);
    }

}