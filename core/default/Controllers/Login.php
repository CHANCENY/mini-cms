<?php

namespace Mini\Cms\default\Controllers;

use Mini\Cms\Controller\ContentType;
use Mini\Cms\Controller\ControllerInterface;
use Mini\Cms\Controller\Request;
use Mini\Cms\Controller\Response;
use Mini\Cms\Controller\StatusCode;
use Mini\Cms\Modules\CurrentUser\Authenticator;
use Mini\Cms\Modules\Storage\Tempstore;
use Symfony\Component\HttpFoundation\RedirectResponse;

class Login implements ControllerInterface
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
        $theme = Tempstore::load('theme_loaded');
        if($this->request->isMethod(\Symfony\Component\HttpFoundation\Request::METHOD_POST)) {
            $login = new Authenticator();
            if($login->loginUser($this->request->getPayload()->get('name'), $this->request->getPayload()->get('password')))
            {
                (new RedirectResponse('/'))->send();
                exit;
            }
        }
        $this->response->setContentType(ContentType::TEXT_HTML)
            ->setStatusCode(StatusCode::OK)
            ->write($theme->view('login_form.php', []));
    }
}