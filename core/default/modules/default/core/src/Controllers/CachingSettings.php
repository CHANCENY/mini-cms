<?php

namespace Mini\Cms\default\modules\default\core\src\Controllers;

use Mini\Cms\Mini;
use Mini\Cms\Modules\Extensions\ModuleHandler\ModuleHandler;
use Mini\Cms\Modules\Site\Site;
use Mini\Cms\Routing\Route;
use Mini\Cms\Services\Services;
use Mini\Cms\Controller\Request;
use Mini\Cms\Controller\Response;
use Mini\Cms\Modules\Cache\Caching;
use Mini\Cms\Configurations\ConfigFactory;
use Mini\Cms\Connections\Database\Database;
use Mini\Cms\Modules\Extensions\Extensions;
use Mini\Cms\Controller\ControllerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request as RequestAlias;

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
        if($this->request->isMethod(RequestAlias::METHOD_POST)) {
            if($this->request->request->has('database')) {
                $payload = $this->request->getPayload();
                $config = new ConfigFactory();
                $database = [
                    'db_host' => $payload->get('db_host'),
                    'db_user' => $payload->get('db_user'),
                    'db_password' => $payload->get('db_password'),
                    'db_name' => $payload->get('db_name'),
                    'db_type' => $payload->get('db_type'),

                ];
                $config->set('database', $database);
                if($config->save(true)) {
                    (new RedirectResponse('/settings'))->send();
                }
            }
            if($this->request->request->has('site')) {
                $payload = $this->request->getPayload();
                $site = new Site();
                $site->setBrandingAssets('Phone', $payload->get('Phone', $site->getBrandingAssets('Phone')));
                $site->setBrandingAssets('Email', $payload->get('Email', $site->getBrandingAssets('Email')));
                $site->setBrandingAssets("Name", $payload->get('Name', $site->getBrandingAssets('Name')));
                $logo = $payload->get('Logo', null);
                if(empty($logo)) {
                    $logo = $site->getBrandingAssets('Logo');
                }else {
                    $logo = ['fid'=>$logo];
                }
                $site->setBrandingAssets("Logo", $logo);
                if($site->save()) {
                    (new RedirectResponse('/settings'))->send();
                }
            }

            if($this->request->request->has('smtp')) {
                $payload = $this->request->getPayload();
                $site = new Site();
                $smtp = [
                    'smtp_server' => $payload->get('smtp_server', $site->getSmtpInformation('smtp_server')),
                    'smtp_port' => $payload->get('smtp_port', $site->getSmtpInformation('smtp_port')),
                    'smtp_username' => $payload->get('smtp_username', $site->getSmtpInformation('smtp_username')),
                    'smtp_password' => $payload->get('smtp_password', $site->getSmtpInformation('smtp_password')),
                ];
                $site->setContactInformation("Smtp", $smtp);
                if($site->save()) {
                    (new RedirectResponse('/settings'))->send();
                }
            }
        }

        $extend_controller = new Route('905d8ebb-105f-4d3a-801a-67e8c83150d0');
        $role_controller = new Route('6335a6e9-2cd2-4914-ba9e-fc3dac-e12b29--09877765985-884747');
        $theme_controller = new Route('cf2fcaa9-f729-4dc3-a348-256cb9-theme-extend');
        $response = $extend_controller->loadController();
        $response_r = $role_controller->loadController();
        $response_t = $theme_controller->loadController();
        $this->response->write(
            Services::create('render')
            ->render('development-dashboard.php',
                ['database'=>new Database(true),
                    'site'=>new Site(),
                    'extend'=>$response->getBody(),
                    'role_controller'=>$response_r->getBody(),
                    'theme_controller'=>$response_t->getBody(),
                ]
            )
        );
    }

    public function settingsAction(): void
    {
        $setting_action = $this->request->get('setting_name');
        if($setting_action) {

            switch($setting_action) {

                case 'clear':
                    Caching::cache()->clear();
                    Mini::messenger()->addMessage('Setting cache cleared');
                    break; 
                case 'servicesregister':
                    Extensions::bootServices();
                    Mini::messenger()->addMessage("Services registered successfully.");
                    break;
                case 'routesregister':
                    $routes = Extensions::importRoutes(); 
                    Caching::cache()->set('system-routes',$routes);
                    Mini::messenger()->addMessage("Routes registered successfully");
                    break; 
                case 'menus':
                    $menus = Extensions::bootMenus();
                    Caching::cache()->set('system-menus',$menus);
                    Mini::messenger()->addMessage("Menus registered successfully");
                    break;
                case 'etagsregister':
                    $modules = Extensions::activeModules();
                    $flag = [];
                    foreach ($modules as $module) {
                        if($module instanceof ModuleHandler) {
                            $flag[] = $module->eTagRegisterRoutes();
                        }
                    }
                    if(in_array(true, $flag)) {
                        Mini::messenger()->addMessage("ETag data generated successfully");
                    }else {
                        Mini::messenger()->addMessage("ETag data already generated");
                    }
                    break;
            }

            (new RedirectResponse('/settings', 308))->send();
        }
    }

}