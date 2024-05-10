<?php

namespace Mini\Cms\default\Controllers;

use Mini\Cms\Controller\ContentTypeEnum;
use Mini\Cms\Controller\ControllerInterface;
use Mini\Cms\Controller\Request;
use Mini\Cms\Controller\Response;
use Mini\Cms\Controller\StatusCodeEnum;
use Mini\Cms\Modules\FileSystem\File;
use Mini\Cms\Services\Services;

class UserRegistration implements ControllerInterface
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
        // TODO: Implement writeBody() method.
       // Services::create('messenger')->addMessage('Hello testing theme messenger');

        $this->response->setContentType(ContentTypeEnum::TEXT_HTML)
            ->setStatusCode(StatusCodeEnum::OK)
            ->write(Services::create('messenger')->getMessages());
    }
}