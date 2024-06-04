<?php

namespace Mini\Cms\default\Controllers;

use Mini\Cms\Controller\ContentType;
use Mini\Cms\Controller\ControllerInterface;
use Mini\Cms\Controller\Request;
use Mini\Cms\Controller\Response;
use Mini\Cms\Controller\StatusCode;
use Mini\Cms\Entities\User;
use Mini\Cms\Modules\CurrentUser\Authenticator;
use Mini\Cms\Modules\CurrentUser\CurrentUser;
use Mini\Cms\Services\Services;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ProfileDelete implements ControllerInterface
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

        $action = $this->request->get('action');

        if(empty($action)) {
            $this->response->setContentType(ContentType::TEXT_HTML)
                ->setStatusCode(StatusCode::OK)
                ->write(Services::create('render')->render('confirmation_page.php',['title'=>'Are you sure you want to delete this user and all its content?']));
        }

        if((int) $action === 1) {
            if($user->delete()) {
                if($currentUser->id() == $user->getUid()) {
                    $auth = new Authenticator();
                    $auth->logoutUser();
                }
                (new RedirectResponse('/'))->send();
                exit;
            }
        }

        if ($action !== null && (int) $action === 0) {
            (new RedirectResponse('/user/'.$user->getUid()))->send();
        }
    }
}