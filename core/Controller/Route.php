<?php

namespace Mini\Cms\Controller;

use Mini\Cms\Configurations\ConfigFactory;
use Mini\Cms\Modules\Access\AccessMiddleRunner;
use Mini\Cms\Modules\Extensions\Extensions;
use Mini\Cms\Modules\Extensions\ModuleHandler\ModuleHandler;
use Mini\Cms\Modules\MetaTag\MetaTag;
use Mini\Cms\Modules\Storage\Tempstore;
use Mini\Cms\Routing\RouteBuilder;
use Mini\Cms\Routing\URIMatcher;
use Mini\Cms\Services\Services;
use Mini\Cms\Theme\Footer;
use Mini\Cms\Theme\Menus;
use Mini\Cms\Theme\Theme;
use Symfony\Component\HttpFoundation\RedirectResponse;

class Route
{

    /**
     * @var false|mixed
     */
    private mixed $loadedRoute;
    private Request $request;
    private Response $response;
    private mixed $controllerHandler;
    private string $currentUri;

    public function getResponse(): Response
    {
        return $this->response;
    }

    public function getControllerHandler(): mixed
    {
        return $this->controllerHandler;
    }

    public function getCurrentUri(): string
    {
        return $this->currentUri;
    }

    /**
     * @throws PageNotFoundException
     * @throws TemporaryUnAvailableException
     * @throws AccessDeniedRouteException
     * @throws ControllerMissingException
     * @throws ControllerHandlerNotFoundException
     * @throws BadGateWayException
     */
    public function match(string $method, string $path): void
    {
        // Prepare uri
        $path = "/". trim($path, '/');

        // Getting all routes.
        $builder = new RouteBuilder();
        $matcher = new URIMatcher($builder->getPatterns());
        if ($matcher->matchCurrentURI($path)) {
            $params = $matcher->getParams();
            $pattern = $matcher->getMatchedPattern();

            $this->currentUri = $path;

            Tempstore::save('current_route',$this);
            $theme = Theme::loader();
            Extensions::runHooks('_theme_alter',[&$theme]);


            Tempstore::save('theme_loaded',$theme);

            $menus = new Menus($path);
            Extensions::runHooks('_menus_alter',[&$menus]);
            Tempstore::save('theme_navigation', $menus);

            $footer = new Footer();
            $footer->themeFile('footer.php');
            Extensions::runHooks('_footer_alter',[&$footer]);
            Tempstore::save('theme_footer', $footer);

            $metaTag = new MetaTag();
            Extensions::runHooks('_meta_data_initialize_alter',[&$metaTag]);
            Tempstore::save('theme_meta_tags', $metaTag);

            if(!empty($params)) {
                $_GET = array_merge($_GET, $params);
                Extensions::runHooks('_request_params_alter',[&$_GET]);
            }

            // Found matched route info
            $routeBuilder = new RouteBuilder();
            $this->loadedRoute = $routeBuilder->getRouteByPattern($pattern);
            Extensions::runHooks('_loaded_route_alter',[&$this->loadedRoute]);

            // Let's load controller here.
            $controller = $this->loadedRoute->getRouteHandler();

            // Handling controller.
            if(empty($controller)) {
                throw new ControllerHandlerNotFoundException("Controller not found to handle the request CID: ".$this->loadedRoute->getRouteId());
            }
            $this->request = Request::createFromGlobals();
            $this->response = new Response();

            // Middlewares calling.
            $access_middle_response = (new AccessMiddleRunner($this->loadedRoute, $this->response))->runMiddleWares();
            if($access_middle_response) {
                $access_middle_response->send();
                exit;
            }

            // Before calling handle lets check options
            if(!$this->loadedRoute->isAccessible()) {
                throw new TemporaryUnAvailableException("This controller can not be access at moment CD". $this->loadedRoute->getRouteId());
            }

            if(!$this->loadedRoute->isMethod($method)) {
                throw new BadGateWayException('Method '.$method. ' is not allowed for route CD: '.$this->loadedRoute->getRouteId());
            }

            // Current user here.
            $currentUserRoles = Services::create('current.user')->getRoles();
            if(!$this->loadedRoute->isUserAllowed($currentUserRoles)) {
                throw new AccessDeniedRouteException("Route is not allowed to be access by user with ".implode(',', $currentUserRoles). ' roles RD: '.$this->loadedRoute->getRouteId());
            }

            // Making handler object.
            $this->controllerHandler = new $controller($this->request, $this->response);
            if($this->controllerHandler instanceof ControllerInterface) {

                if($this->controllerHandler->isAccessAllowed()) {

                    // Will are calling writeBody method on controller class.
                    // so that we can have a response.
                    $this->controllerHandler->writeBody();
                    Extensions::runHooks('_response_alter',[&$this->response]);
                    $this->response->send();
                    exit;
                }
            }

            // If reach at this point the dont have controller.
            throw new ControllerMissingException("Controller not found (".$controller. ")");
        }
        else {
            Extensions::runHooks('_not_found_alter',[&$path]);
            throw new PageNotFoundException("This uri is missing in system $path");
        }
    }

    public static function app(string $method, string $request_uri): void
    {
        (new Route())->match($method,$request_uri);
    }

    public function getLoadedRoute(): mixed
    {
        return $this->loadedRoute;
    }

    public function __construct()
    {
        $config = Services::create('config.factory');
        if($config instanceof ConfigFactory) {
            $database = $config->get('database');
            if(empty($database)) {
                (new RedirectResponse('/new-install.php'))->send();
                exit;
            }
        }
        Extensions::runHooks('_wrapper_register_alter',[]);
    }
}