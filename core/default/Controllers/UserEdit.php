<?php

namespace Mini\Cms\default\Controllers;

use Mini\Cms\Controller\ContentType;
use Mini\Cms\Controller\ControllerInterface;
use Mini\Cms\Controller\Request;
use Mini\Cms\Controller\Response;
use Mini\Cms\Controller\StatusCode;
use Mini\Cms\Entities\User;
use Mini\Cms\Services\Services;
use Symfony\Component\HttpFoundation\RedirectResponse;

class UserEdit implements ControllerInterface
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
        if($this->request->isMethod(\Symfony\Component\HttpFoundation\Request::METHOD_POST)) {
            $user = User::load($this->request->get('uid'));

            if($this->request->getPayload()->get('firstname')) {
                $user->setFirstname($this->request->getPayload()->get('firstname'));
            }

            if($this->request->getPayload()->get('lastname')) {
                $user->setLastname($this->request->getPayload()->get('lastname'));
            }

            if($this->request->getPayload()->get('email')){
                $user->setEmail($this->request->getPayload()->get('email'));
            }

            if($this->request->getPayload()->get('username')){
                $user->setName($this->request->getPayload()->get('username'));
            }

            if($this->request->getPayload()->get('image')) {
                $user->setImage($this->request->getPayload()->get('image'));
            }


            if($user->update()) {
                (new RedirectResponse('/user/'.$user->getUid()))->send();
                exit;
            }
        }
        $user = User::load($this->request->get('uid'));
        $this->response->setContentType(ContentType::TEXT_HTML)->setStatusCode(StatusCode::OK)
            ->write(Services::create('render')->render('user_edit_form.php',['user' => $user]));
    }
}