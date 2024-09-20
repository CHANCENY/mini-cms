<?php

/**
@route Mini\Cms\Web\Controllers\Young

*/
namespace Mini\Cms\Web\Controllers\Young;

use Mini\Cms\Controller\ControllerInterface;
use Mini\Cms\Controller\Request;
use Mini\Cms\Controller\Response;

use Mini\Cms\Controller\StatusCode;

use Mini\Cms\Controller\ContentType;

class Young implements ControllerInterface {

    public function __construct(private Request &$request, private Response &$response){}

    public function isAccessAllowed(): bool { return true; }

 public function writeBody(): void
{
         $ul = "<ul>";
         for ($i = 0; $i < 100000; $i++) {
             $ul .= "<li>".$i ." Item $i</li>";
         }
         $ul .= "</ul>";
        $this->response->setStatusCode(StatusCode::OK)->setContentType(ContentType::TEXT_HTML)->write($ul);    }
}

