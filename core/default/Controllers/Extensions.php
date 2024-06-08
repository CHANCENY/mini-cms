<?php

namespace Mini\Cms\default\Controllers;

use Mini\Cms\Controller\ControllerInterface;
use Mini\Cms\Controller\Request;
use Mini\Cms\Controller\Response;
use Mini\Cms\Modules\Extensions\ModuleHandler\Installer;
use Mini\Cms\Services\Services;

class Extensions implements ControllerInterface
{

    public function __construct(private Request &$request, private Response &$response)
    {
    }

    public function isAccessAllowed(): bool
    {
        return true;
    }

    public function writeBody(): void
    {
        if($this->request->isMethod(\Symfony\Component\HttpFoundation\Request::METHOD_POST)) {
            $ext_type = $this->request->getPayload()->get('type');
            $zip_file = $this->request->getPayload()->get('zip_file');
            if($ext_type == 'module') {
               $module_path = \Mini\Cms\Modules\Extensions\Extensions::extensionsPrepareModule((int) $zip_file);

               if(!empty($module_path)) {
                   $installer = new Installer($module_path);
                   $installer->installModuleSchema();
                   $installer->saveModuleSchema();
               }

            }
        }
        $this->response->write(Services::create('render')->render('extensions.php'));
    }
}