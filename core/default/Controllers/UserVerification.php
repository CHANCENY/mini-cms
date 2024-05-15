<?php

namespace Mini\Cms\default\Controllers;

use Mini\Cms\Controller\ContentType;
use Mini\Cms\Controller\ControllerInterface;
use Mini\Cms\Controller\Request;
use Mini\Cms\Controller\Response;
use Mini\Cms\Controller\StatusCode;
use Mini\Cms\Modules\CurrentUser\Authenticator;

class UserVerification implements ControllerInterface
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
        $token = $this->request->get('token');
        if($token === null){
            $this->response->setContentType(ContentType::TEXT_HTML)
                ->setStatusCode(StatusCode::OK)
                ->write('<div class="container mt-lg-5 bordered rounded bg-light p-5 text-center"><h3>Sorry token is missing</h3></div>');
        }

        $auth = new Authenticator();
        if($auth->verifyToken($token)) {
            $this->response->setContentType(ContentType::TEXT_HTML)
                ->setStatusCode(StatusCode::OK)
                ->write('<div class="container mt-lg-5 bordered rounded bg-light p-5 text-center"><h3>Verification was successfully.</h3></div>');
        }
        else {
            $this->response->setContentType(ContentType::TEXT_HTML)
                ->setStatusCode(StatusCode::OK)
                ->write('<div class="container mt-lg-5 bordered rounded bg-light p-5 text-center"><h3>Verification failed.</h3></div>');
        }
    }
}