<?php

namespace Mini\Cms\default\modules\default\core\src\Controllers;

use Mini\Cms\Mini;
use Mini\Cms\Services\Services;
use Mini\Cms\Controller\Request;
use Mini\Cms\Controller\Response;
use Mini\Cms\Modules\Themes\ThemeExtension;
use Mini\Cms\Controller\ControllerInterface;
use Mini\Cms\Modules\FileSystem\File;
use Symfony\Component\HttpFoundation\RedirectResponse;

class Theming implements ControllerInterface
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

            if($this->request->request->has('new_theme')) {
                $payload = $this->request->getPayload();
                if(ThemeExtension::createTheme($payload->get('title'), $payload->get('version'), $payload->get('description')))
                {
                    Mini::messenger()->addMessage("Theme Created successfully");
                } else {
                    Mini::messenger()->addErrorMessage("Failed to add the theme");
                }
            }

            if($this->request->request->has('theme_upload')) {
                $theme_zip = $this->request->request->get('theme_zip');
                if(!empty($theme_zip) && is_numeric($theme_zip)) {
                    $file = File::load($theme_zip);
                    $zip_file = $file->getFilePath();
                    if(ThemeExtension::prepareThemeInstall($zip_file)) {
                        Mini::messenger()->addMessage("Theme uploaded successfully");
                    }
                    else {
                        Mini::messenger()->addErrorMessage("Failed to add the theme");
                    }
                }
            }
            (new RedirectResponse($this->request->headers->get('referer'), 308))->send();
        }
        $themes = ThemeExtension::bootThemes();
        $this->response->write(
            Services::create('render')
            ->render('dashboard-theme-view.php',['themes' => $themes])
        );
    }

    public function themeStatusUpdateController(): void
    {
        $theme_name = $this->request->get('theme_name');
        $status = $this->request->get('status_value', 0);
        if($theme_name) {
            if(ThemeExtension::enableTheme($theme_name, $status)) {
                Mini::messenger()->addMessage("Theme updated successfully");
            }else {
                Mini::messenger()->addErrorMessage("Failed to update the theme");
            }
        }
        (new RedirectResponse($this->request->headers->get('referer'), 308))->send();
    }


}