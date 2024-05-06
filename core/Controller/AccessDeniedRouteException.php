<?php

namespace Mini\Cms\Controller;

class AccessDeniedRouteException extends \Exception implements ControllerErrorInterface
{
    public function getStatusCode(): int
    {
       return 401;
    }

    public function getContentType(): string
    {
        return "text/html";
    }

    public function getContent(): string
    {
        return "<h2>Access Denied</h2>";
    }
}