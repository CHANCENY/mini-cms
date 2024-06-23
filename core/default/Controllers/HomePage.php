<?php

namespace Mini\Cms\default\Controllers;

use Mini\Cms\Controller\ContentType;
use Mini\Cms\Controller\ControllerInterface;
use Mini\Cms\Controller\Request;
use Mini\Cms\Controller\Response;
use Mini\Cms\Controller\StatusCode;
use Mini\Cms\Modules\Access\Roles;
use Mini\Cms\Services\Services;

class HomePage implements ControllerInterface
{

    public function __construct(private Request &$request, private Response &$response)
    {
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
            ->write(Services::create('render')->render('home_page.php'));
    }
}