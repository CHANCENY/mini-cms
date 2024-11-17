<?php

namespace Mini\Cms\default\modules\default\core\src\Controllers;

use Mini\Cms\Controller\ControllerInterface;
use Mini\Cms\Controller\Request;
use Mini\Cms\Controller\Response;
use Mini\Cms\Routing\Route;
use Mini\Cms\Services\Services;

class PeopleSettings implements ControllerInterface {

    public function __construct(private Request &$request, private Response &$response)
    {
        
    }

    public function isAccessAllowed(): bool
    {
        return true;
    }

    public function writeBody(): void
    {

        $controller_people = new Route('6335a6e9-2cd2-4914-ba9e-fc3dac-e12b29-5985-884747');
        $controller_role = new Route('6335a6e9-2cd2-4914-ba9e-fc3dac-e12b29--09877765985-884747');
        $response_role = $controller_role->loadController();
        $response_people = $controller_people->loadController();
        $this->response->write(
            Services::create('render')
            ->render('people-settings-dashoard.php',
            [
                'people'=>$response_people->getBody(),
                'roles' => $response_role->getBody()
            ]
            )
        );
    }
}