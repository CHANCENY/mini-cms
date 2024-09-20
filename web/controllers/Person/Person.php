<?php

/**
@route Mini\Cms\Web\Controllers\Person

*/
namespace Mini\Cms\Web\Controllers\Person;

use Mini\Cms\Controller\ControllerInterface;
use Mini\Cms\Controller\Request;
use Mini\Cms\Controller\Response;

use Mini\Cms\Controller\StatusCode;

use Mini\Cms\Controller\ContentType;

class Person implements ControllerInterface {

    public function __construct(private Request &$request, private Response &$response){}

    public function isAccessAllowed(): bool { return true; }

 public function writeBody(): void
{
        $this->response->setStatusCode(StatusCode::OK)->setContentType(ContentType::TEXT_HTML)->write("<h1>Hello World!</h1>");    }
}

