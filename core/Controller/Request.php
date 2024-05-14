<?php

namespace Mini\Cms\Controller;

class Request extends \Symfony\Component\HttpFoundation\Request
{
    public function __construct(array $query = [], array $request = [], array $attributes = [], array $cookies = [], array $files = [], array $server = [], $content = null)
    {
        parent::__construct($query, $request, $attributes, $cookies, [], $server, $content);
    }
}