<?php

namespace Mini\Cms\default\Controllers;

use Mini\Cms\Controller\ContentType;
use Mini\Cms\Controller\ControllerInterface;
use Mini\Cms\Controller\Request;
use Mini\Cms\Controller\Response;
use Mini\Cms\Controller\StatusCode;
use Mini\Cms\Modules\CurrentUser\Authenticator;
use Mini\Cms\Services\Services;
use Symfony\Component\HttpFoundation\RedirectResponse;

class LogOut implements ControllerInterface
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
        $auth = Services::create('authenticator');
        if($auth instanceof Authenticator) {

            if($auth->logoutUser()) {
                (new RedirectResponse('/'))->send();
                exit;
            }
        }

        $this->response->setStatusCode(StatusCode::OK)
            ->setContentType(ContentType::TEXT_HTML)
            ->write("<h2>Sorry something went wrong! During logout process</h2>");
    }
}