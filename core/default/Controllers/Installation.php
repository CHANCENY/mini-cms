<?php

namespace Mini\Cms\default\Controllers;

use Mini\Cms\Configurations\ConfigFactory;
use Mini\Cms\Connections\Database\Database;
use Mini\Cms\Controller\ContentType;
use Mini\Cms\Controller\ControllerInterface;
use Mini\Cms\Controller\Request;
use Mini\Cms\Controller\Response;
use Mini\Cms\Controller\StatusCode;
use Mini\Cms\Modules\Storage\Tempstore;
use Mini\Cms\Services\Services;
use Mini\Cms\Theme\Theme;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\RedirectResponse;

class Installation implements ControllerInterface
{

    private array $options;

    public function __construct(private Request &$request, private Response &$response)
    {
        $this->response->setContentType(ContentType::TEXT_HTML);
        $this->options = [
            'current_route' => Tempstore::load('current_route'),
        ];
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
        if($this->request->getMethod() == 'POST') {
            $payload = $this->request->getPayload();
            $config = Services::create('config.factory');

            // Check if all good
            if($config instanceof ConfigFactory) {
                $database = [
                    'db_host' => $payload->get('db_host'),
                    'db_user' => $payload->get('db_user'),
                    'db_password' => $payload->get('db_password'),
                    'db_name' => $payload->get('db_name'),
                    'db_type' => $payload->get('db_type'),

                ];
                $config->set('database', $database);
                if($config->save(true)) {
                    $this->response->setStatusCode(StatusCode::PERMANENT_REDIRECT);
                    (new RedirectResponse('/site-configuration',StatusCode::PERMANENT_REDIRECT->value))->send();
                    exit;
                }
            }

        }
        $this->response->setStatusCode(StatusCode::NOT_FOUND);
        $theme = Tempstore::load('theme_loaded');

        if (!empty($theme) && $theme instanceof Theme) {
            $view = $theme->view('install_form.php',$this->options);

            if($view) {
                $this->response->setStatusCode(StatusCode::OK);
                $this->response->write($view);
            }
        }
    }
}