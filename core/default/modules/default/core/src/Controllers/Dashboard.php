<?php

namespace Mini\Cms\default\modules\default\core\src\Controllers;

use Mini\Cms\Controller\ContentType;
use Mini\Cms\Controller\ControllerInterface;
use Mini\Cms\Controller\Request;
use Mini\Cms\Controller\Response;
use Mini\Cms\Controller\StatusCode;
use Mini\Cms\Modules\MetaTag\MetagEnum;
use Mini\Cms\Modules\MetaTag\MetaTag;
use Mini\Cms\Modules\Storage\Tempstore;
use Mini\Cms\Services\Services;

class Dashboard implements ControllerInterface
{

    public function __construct(private Request &$request, private Response &$response)
    {
    }

    /**
     * @inheritDoc
     */
    public function isAccessAllowed(): bool
    {
        // TODO: Implement isAccessAllowed() method.
        return true;
    }

    public function writeBody(): void
    {
        $this->response->setStatusCode(StatusCode::OK);
        $this->response->setContentType(ContentType::TEXT_HTML);
        $this->response->write(Services::create('render')->render('default_dashboard.php',[]));
    }
}