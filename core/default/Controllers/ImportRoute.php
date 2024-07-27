<?php

namespace Mini\Cms\default\Controllers;

use Mini\Cms\Controller\ControllerInterface;
use Mini\Cms\Controller\Request;
use Mini\Cms\Controller\Response;
use Mini\Cms\Modules\CurrentUser\Authenticator;
use Mini\Cms\Modules\CurrentUser\CurrentUser;
use Mini\Cms\Modules\Extensions\ModuleHandler\ModuleHandler;

class ImportRoute implements ControllerInterface
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
        if($this->request->getMethod() === 'POST') {
            $username = $this->request->request->get('username');
            $password = $this->request->request->get('password');
            if($username !== null && $password !== null) {
                $auth = new Authenticator();
                if($auth->loginUser($username, $password)) {
                    $currentUser = new CurrentUser();
                    if($currentUser->isAdmin()) {
                        $modules = \Mini\Cms\Modules\Extensions\Extensions::activeModules();
                        foreach ($modules as $module) {
                            if($module instanceof ModuleHandler){
                                $importTerminal = new \Mini\Cms\Modules\Terminal\ImportRoute(['from'=>$module->getName()]);
                                $importTerminal->run();
                            }
                        }
                    }
                }
            }
        }
    }
}