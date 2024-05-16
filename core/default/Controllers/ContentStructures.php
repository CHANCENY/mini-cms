<?php

namespace Mini\Cms\default\Controllers;

use Mini\Cms\Controller\ContentType;
use Mini\Cms\Controller\ControllerInterface;
use Mini\Cms\Controller\Request;
use Mini\Cms\Controller\Response;
use Mini\Cms\Controller\StatusCode;
use Mini\Cms\Entity;
use Mini\Cms\Services\Services;

class ContentStructures implements ControllerInterface
{

    public function __construct(private Request &$request, private Response &$response)
    {
    }

    public function isAccessAllowed(): bool
    {
        return true;
    }

    public function writeBody(): void
    {
        $render = Services::create('render');
        $this->response->setContentType(ContentType::TEXT_HTML);
        $this->response->setStatusCode(StatusCode::OK);

        $entities = Entity::entities();

        $this->response->write($render->render('content_types_listing.php',['entities'=>$entities]));
    }
}