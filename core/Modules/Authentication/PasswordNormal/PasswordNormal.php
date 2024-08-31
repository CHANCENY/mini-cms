<?php

namespace Mini\Cms\Modules\Authentication\PasswordNormal;

use Mini\Cms\Mini;
use Mini\Cms\Modules\Authentication\AuthenticationInterface;
use Mini\Cms\Modules\CurrentUser\Authenticator;
use Mini\Cms\Modules\CurrentUser\CurrentUser;
use Mini\Cms\Routing\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class PasswordNormal implements AuthenticationInterface
{

    private bool|null $is_authenticated = null;

    public function getTheme(): string
    {
        return "login_form.php";
    }

    public function authenticate(Request $request): void
    {
        if($request->isMethod(\Symfony\Component\HttpFoundation\Request::METHOD_POST)) {
            $login = new Authenticator();
            if($login->loginUser($request->getPayload()->get('name'), $request->getPayload()->get('password'))) {
                Mini::messenger()->addSuccessMessage("Welcome back! ".(new CurrentUser())->getFirstName());
                $this->is_authenticated = true;
            }
            else {
                Mini::messenger()->addErrorMessage("Username or password is incorrect");
                $this->is_authenticated = false;
            }
        }
    }

    public function success(Route $success_route): void
    {
        if($this->is_authenticated) {
            (new RedirectResponse($success_route->getUrl()))->send();
        }
    }

    public function error(Route $error_route): void
    {
        if($this->is_authenticated === false) {
            (new RedirectResponse($error_route->getUrl()))->send();
            exit;
        }
    }
}