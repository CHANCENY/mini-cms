<?php

namespace Mini\Cms\default\Controllers;

use Mini\Cms\Controller\ContentType;
use Mini\Cms\Controller\ControllerInterface;
use Mini\Cms\Controller\Request;
use Mini\Cms\Controller\Response;
use Mini\Cms\Controller\StatusCode;
use Mini\Cms\Modules\CurrentUser\Authenticator;
use Mini\Cms\Modules\Messenger\Messenger;
use Mini\Cms\Services\Services;
use Mini\Cms\Theme\Render;

class Password implements ControllerInterface
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
        if($this->request->isMethod(\Symfony\Component\HttpFoundation\Request::METHOD_POST)) {
            $email = $this->request->getPayload()->get('email_address');
            $auth = new Authenticator();
            $auth->passwordResetToken($email);
        }
        $this->response->setContentType(ContentType::TEXT_HTML)
            ->setStatusCode(StatusCode::OK)
            ->write($render->render('password_form.php',[]));
    }
}