<?php

namespace Mini\Cms\default\modules\default\core\src\Controllers;

use Mini\Cms\Controller\ControllerInterface;
use Mini\Cms\Controller\Request;
use Mini\Cms\Controller\Response;
use Mini\Cms\Entities\User;
use Mini\Cms\Modules\Access\Roles;
use Mini\Cms\Services\Services;
use Symfony\Component\HttpFoundation\RedirectResponse;

class People implements ControllerInterface
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
       $this->response->write(Services::create('render')->render('default_people.php',['users' => User::users()]));
    }

    public function roles(): void
    {
        $roles = new Roles();
        $this->response->write(Services::create('render')->render('default_roles.php',['roles' => $roles->getRoles()]));
    }

    public function newRole(): void
    {
        if($this->request->isMethod('POST')) {
            $role = new Roles();
            $role->setRoles([
                'label' => $this->request->request->get('label'),
                'name' => $this->request->request->get('name'),
                'permissions' => $this->request->request->all('permissions')
            ]);
            $role->saveRole();
            (new RedirectResponse($this->request->headers->get('referer')))->send();
            exit;
        }
        $this->response->write(Services::create('render')->render('default_role_new.php'));
    }
}