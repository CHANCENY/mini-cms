<?php

namespace Mini\Cms\Web\Controllers;

use Mini\Cms\Controller\ContentTypeEnum;
use Mini\Cms\Controller\ControllerInterface;
use Mini\Cms\Controller\Request;
use Mini\Cms\Controller\Response;
use Mini\Cms\Controller\Route;
use Mini\Cms\Controller\StatusCodeEnum;
use Mini\Cms\Modules\Storage\Tempstore;
use Mini\Cms\Routing\RouteBuilder;
use Mini\Cms\Theme\Menus;

class ContactUs implements ControllerInterface
{

    private Response  $response;

    private Request $request;

    public function __construct(Request &$request, Response &$response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    public function isAccessAllowed(): bool
    {
        return true;
    }

    public function writeBody(): void
    {

        $this->response->setStatusCode(StatusCodeEnum::OK)
            ->setContentType(ContentTypeEnum::TEXT_HTML)
            ->write("<p>Hello am implement new cms</p>");
    }
}