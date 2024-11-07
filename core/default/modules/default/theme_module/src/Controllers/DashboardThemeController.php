<?php

namespace Mini\Cms\default\modules\default\theme_module\src\Controllers;

use ThemeBuilder;
use Mini\Cms\Mini;
use Mini\Cms\Services\Services;
use Mini\Cms\Controller\Request;
use Mini\Cms\Controller\Response;
use Mini\Cms\Controller\ControllerInterface;

class DashboardThemeController implements ControllerInterface {

    public function __construct(private Request &$request, private Response &$response){}


    public function isAccessAllowed(): bool
    {
        return true;   
    }


    public function writeBody(): void
    {
        $this->response->write(
            Services::create('render')
            ->render('theme-module-dashboard.php')
        );
    }

    public function newThemeController(): void 
    {

        if($this->request->isMethod('POST')) {

            $data = $this->request->request->all();
            if(!empty($data['theme_title']) && !empty($data['theme_image'])) {
                /**@var $theme_builder ThemeBuilder */
                $theme_builder = Services::create('theme.builder');

                $theme_builder->setTitle($data['theme_title']);
                $theme_builder->setDescription($data['description']);
                $theme_builder->setIcon($data['theme_image']);
                $theme_builder->setSourceDirectory();
                if($theme_builder->make()){
                    Mini::messenger()->addSuccessMessage("Theme added successfully");
                }
                else {
                    Mini::messenger()->addErrorMessage("Failed to add this theme");
                }
                
            }
        }
        $this->response->write(
            Services::create('render')
                ->render('theme-module-dashboard-new.php')
        ); 
    }
}