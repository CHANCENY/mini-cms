<?php

namespace Mini\Cms\default\Controllers;

use Mini\Cms\Controller\ContentType;
use Mini\Cms\Controller\ControllerInterface;
use Mini\Cms\Controller\Request;
use Mini\Cms\Controller\Response;
use Mini\Cms\Controller\StatusCode;
use Mini\Cms\Entities\User;
use Mini\Cms\Modules\CurrentUser\CurrentUser;
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
        $currentUser = new CurrentUser();
        $actions_button = false;
        if($currentUser->isAdmin()) {
            $actions_button = true;
        }
        if($user->getUid()) {

            if($user->getUid() === (int) $currentUser->id()) {
                $actions_button = true;
            }
            $this->response->setContentType(ContentType::TEXT_HTML)
                ->setStatusCode(StatusCode::OK)
                ->write(Services::create('render')->render('user_profile.php',['user' => $user, 'actions_button' => $actions_button]));
        }
    }
}