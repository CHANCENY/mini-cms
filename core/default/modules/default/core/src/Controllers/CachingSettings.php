<?php

namespace Mini\Cms\default\modules\default\core\src\Controllers;

use Mini\Cms\Mini;
use Mini\Cms\Services\Services;
use Mini\Cms\Controller\Request;
use Mini\Cms\Controller\Response;
use Mini\Cms\Modules\Cache\Caching;
use Mini\Cms\Configurations\ConfigFactory;
use Mini\Cms\Connections\Database\Database;
use Mini\Cms\Modules\Extensions\Extensions;
use Mini\Cms\Controller\ControllerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

class CachingSettings implements ControllerInterface
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
        $this->response->write(
            Services::create('render')
            ->render('development-dashboard.php',['database'=>new Database(true)])
        );
    }

    public function settingsAction(): void
    {
        $setting_action = $this->request->get('setting_name');
        if($setting_action) {

            switch($setting_action) {

                case 'clear':
                    Caching::cache()->clear();
                    break; 
                case 'servicesregister':
                    Extensions::bootServices();
                    break;
                case 'routesregister':
                    $routes = Extensions::importRoutes(); 
                    Caching::cache()->set('system-routes',$routes);
                    break; 
                case 'menus':
                    $menus = Extensions::bootMenus();
                    Caching::cache()->set('system-menus',$menus);
                    break;        
            }

            (new RedirectResponse('/settings', 308))->send();
        }
    }

}