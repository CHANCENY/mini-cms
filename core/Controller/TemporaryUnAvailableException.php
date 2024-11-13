<?php

namespace Mini\Cms\Controller;

class TemporaryUnAvailableException extends \Exception implements ControllerErrorInterface
{
    public function getStatusCode(): int
    {
        return 503;
    }

    public function getContentType(): string
    {
        return "text/html";
    }

    public function getContent(): string
    {
        return "<h2>Service not available at moment try again later</h2>";
    }
}