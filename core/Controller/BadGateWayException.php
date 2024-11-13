<?php

namespace Mini\Cms\Controller;

class BadGateWayException extends \Exception implements ControllerErrorInterface
{
    public function getStatusCode(): int
    {
       return 405;
    }

    public function getContentType(): string
    {
        return "text/html";
    }

    public function getContent(): string
    {
        return "<h2>Method not allowed</h2>";
    }
}