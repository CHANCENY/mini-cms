<?php

namespace Mini\Cms\default\modules\default\core\src\Controllers;

use Mini\Cms\Controller\ContentType;
use Mini\Cms\Controller\ControllerInterface;
use Mini\Cms\Controller\Request;
use Mini\Cms\Controller\Response;
use Mini\Cms\Controller\StatusCode;

class Home implements ControllerInterface
{

    private Response $response;
    private Request $request;

    public function __construct(Request &$request, Response &$response)
    {
        $this->response = $response;
        $this->request = $request;
    }

    /**
     * @inheritDoc
     */
    public function isAccessAllowed(): bool
    {
        return true;
    }

    public function writeBody(): void
    {
        $this->response->setStatusCode(StatusCode::OK)
            ->setContentType(ContentType::TEXT_HTML)
            ->write("<p>Hello am implement new cms</p>");
    }
}