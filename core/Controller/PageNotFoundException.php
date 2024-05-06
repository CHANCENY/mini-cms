<?php

namespace Mini\Cms\Controller;

class PageNotFoundException extends \Exception implements ControllerErrorInterface
{
    public function getStatusCode(): int
    {
        return 404;
    }

    public function getContentType(): string
    {
        return "text/html";
    }

    public function getContent(): string
    {
        return "<h2>Page Not Found Oops!</h2>";
    }
}