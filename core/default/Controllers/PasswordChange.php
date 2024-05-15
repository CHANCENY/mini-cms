<?php

namespace Mini\Cms\default\Controllers;

use Mini\Cms\Controller\ContentType;
use Mini\Cms\Controller\ControllerInterface;
use Mini\Cms\Controller\Request;
use Mini\Cms\Controller\Response;
use Mini\Cms\Controller\StatusCode;
use Mini\Cms\Modules\CurrentUser\Authenticator;
use Mini\Cms\Services\Services;

class PasswordChange implements ControllerInterface
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
        $render = Services::create('render');
        $token = $this->request->get('token');
        if(empty($token)) {
            $this->response->setContentType(ContentType::TEXT_HTML)
                ->setStatusCode(StatusCode::OK)
                ->write('<div class="container mt-lg-5 bordered rounded bg-light p-5 text-center"><h3>Token is missing.</h3></div>');
        }

        if($this->request->isMethod(\Symfony\Component\HttpFoundation\Request::METHOD_POST)) {
            $password = $this->request->getPayload()->get('password');
            $confirmPassword = $this->request->getPayload()->get('confirm_password');
            if(!empty($password) && !empty($confirmPassword) && $password === $confirmPassword) {
                $auth = new Authenticator();
                if($auth->changePassword($token, $password)) {
                    $this->response->setContentType(ContentType::TEXT_HTML)
                        ->setStatusCode(StatusCode::OK)
                        ->write('<div class="container mt-lg-5 bordered rounded bg-light p-5 text-center"><h3>Password updated successfully.</h3></div>');
                }
            }
        }

        $this->response->setContentType(ContentType::TEXT_HTML)
            ->setStatusCode(StatusCode::OK)
            ->write($render->render('password_change_form.php',['token'=>$token]));
    }
}