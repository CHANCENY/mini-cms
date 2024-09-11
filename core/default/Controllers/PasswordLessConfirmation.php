<?php

namespace Mini\Cms\default\Controllers;

use Mini\Cms\Controller\ControllerInterface;
use Mini\Cms\Controller\Request;
use Mini\Cms\Controller\Response;
use Mini\Cms\Entities\User;
use Mini\Cms\Mini;
use Mini\Cms\Modules\Authentication\Authentication;
use Mini\Cms\Modules\Authentication\AuthenticationInterface;
use Mini\Cms\Modules\Authentication\AuthenticationMethodNotExistException;
use Mini\Cms\Modules\CurrentUser\CurrentUser;
use Mini\Cms\Modules\Storage\Tempstore;

class PasswordLessConfirmation implements ControllerInterface
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

    /**
     * @throws AuthenticationMethodNotExistException
     */
    public function writeBody(): void
    {
        $_user_old = Tempstore::load('password_less_'.$this->request->get('code'));
        $auth_method = $this->request->get('auth_method');
        $authentication = new Authentication();
        $authentication_method = $authentication->getAuthenticationMethodByName($auth_method);

        /**@var $_callback AuthenticationInterface **/
        $_callback = $authentication_method['_callback'];

        if($_user_old) {
           $user = User::load($_user_old['uid']);
           if($user->getName()) {
               _login_user($user);
               $_callback->setIsAuthenticated(true);
               Mini::messenger()->addSuccessMessage('Logged in successfully!');
               $_callback->success($authentication_method['_success_route']);
               return;
           }
           else {
               Mini::messenger()->addErrorMessage("Failed to log in to your account!");
           }
       }
        $_callback->error($authentication_method['_error_route']);
    }
}