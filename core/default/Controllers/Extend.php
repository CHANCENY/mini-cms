<?php

namespace Mini\Cms\default\Controllers;

use Mini\Cms\Controller\ControllerInterface;
use Mini\Cms\Controller\Request;
use Mini\Cms\Controller\Response;
use Mini\Cms\Services\Services;
use Symfony\Component\HttpFoundation\RedirectResponse;

class Extend implements ControllerInterface
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
        if($this->request->isMethod('POST')) {
            $raw_input = $this->request->getPayload();
            $proccessed_data = [];
            foreach($raw_input as $key => $value) {
                if($value !== 'Save') {
                    $list = explode('_', $key);
                    $proccessed_data[end($list)] = $value;
                }
            }
            if(!empty($proccessed_data)) {
                if(\Mini\Cms\Modules\Extensions\Extensions::saveExtensions($proccessed_data)) {
                    (new RedirectResponse('/extension/extend'))->send();
                    exit;
                }
            }
        }
        $modules = \Mini\Cms\Modules\Extensions\Extensions::loadModules();
        $this->response->write(Services::create('render')->render('extend_module.php',['modules' => $modules]));
    }
}