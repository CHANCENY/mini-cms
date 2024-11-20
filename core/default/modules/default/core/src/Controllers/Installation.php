<?php

namespace Mini\Cms\default\modules\default\core\src\Controllers;

use Mini\Cms\Configurations\ConfigFactory;
use Mini\Cms\Controller\ContentType;
use Mini\Cms\Controller\ControllerInterface;
use Mini\Cms\Controller\Request;
use Mini\Cms\Controller\Response;
use Mini\Cms\Controller\StatusCode;
use Mini\Cms\Modules\Storage\Tempstore;
use Mini\Cms\Services\Services;
use Mini\Cms\Theme\Theme;
use Symfony\Component\HttpFoundation\RedirectResponse;

class Installation implements ControllerInterface
{

    private array $options;

    public function __construct(private Request &$request, private Response &$response)
    {
        $this->response->setContentType(ContentType::TEXT_HTML);
        $this->options = [
            'current_route' => get_global('current_route')
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
                $this->response->setStatusCode(StatusCode::PERMANENT_REDIRECT);
                // Trying to boot up extensions
                try{\Mini\Cms\Modules\Extensions\Extensions::bootRoutes();}catch(\Throwable){
                    echo "<p>Hello looks like your database configuration are saved but booting up routes failed please manually continue to this page</p>
                              <a href='/site-configuration'>Site Configuration</a>";
                    return;
                };
                (new RedirectResponse('/site-configuration',StatusCode::PERMANENT_REDIRECT->value))->send();
                return;
            }
        }
        $this->response->setStatusCode(StatusCode::NOT_FOUND);
        $theme = get_global('theme_loaded');

        if (!empty($theme) && $theme instanceof Theme) {
            $view = $theme->view('install_form.php',$this->options);

            if($view) {
                $this->response->setStatusCode(StatusCode::OK);
                $this->response->write($view);
            }
        }
    }
}