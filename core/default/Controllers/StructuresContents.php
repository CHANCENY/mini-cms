<?php

namespace Mini\Cms\default\Controllers;

use Mini\Cms\Controller\ContentType;
use Mini\Cms\Controller\ControllerInterface;
use Mini\Cms\Controller\Request;
use Mini\Cms\Controller\Response;
use Mini\Cms\Controller\StatusCode;
use Mini\Cms\Entities\Node;
use Mini\Cms\Services\Services;

class StructuresContents implements ControllerInterface {


    public function __construct(private Request &$request, private Response &$response)
    {
    }

    public function isAccessAllowed(): bool
    {
        return true;
    }

    public function writeBody(): void
    {
        $nodes = Node::all();
        $this->response->setContentType(ContentType::TEXT_HTML)
             ->setStatusCode(StatusCode::OK)
             ->write(Services::create('render')->render('structure_contents.php', ['nodes'=>$nodes]));
    }
}