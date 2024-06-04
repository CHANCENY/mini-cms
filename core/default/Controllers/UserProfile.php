<?php

namespace Mini\Cms\default\Controllers;

use Mini\Cms\Controller\ContentType;
use Mini\Cms\Controller\ControllerInterface;
use Mini\Cms\Controller\Request;
use Mini\Cms\Controller\Response;
use Mini\Cms\Controller\StatusCode;
use Mini\Cms\Entities\User;
use Mini\Cms\Services\Services;

class UserProfile implements ControllerInterface
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
        $user = User::load($this->request->get('uid'));
        if($user->getUid()) {

            $this->response->setContentType(ContentType::TEXT_HTML)
                ->setStatusCode(StatusCode::OK)
                ->write(Services::create('render')->render('user_profile.php',['user' => $user]));
        }
    }
}