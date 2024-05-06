<?php

namespace Mini\Cms\Controller;

class Request extends \Symfony\Component\HttpFoundation\Request
{
    public static function createFromGlobals(): static
    {
        return new static($_SERVER, $_GET, $_POST, $_COOKIE, $_FILES);
    }
}