<?php

namespace Mini\Cms\Controller;

use Mini\Cms\Modules\Access\AccessMiddleRunner;
use Mini\Cms\Modules\MetaTag\MetaTag;
use Mini\Cms\Modules\Storage\Tempstore;
use Mini\Cms\Routing\RouteBuilder;
use Mini\Cms\Routing\URIMatcher;
use Mini\Cms\Services\Services;
use Mini\Cms\Theme\Footer;
use Mini\Cms\Theme\Menus;
use Mini\Cms\Theme\Theme;

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
            //TODO: call theme modifier hooks.

            Tempstore::save('theme_loaded',$theme);

            $menus = new Menus($path);
            //TODO: call menu_alter
            Tempstore::save('theme_navigation', $menus);

            $footer = new Footer();
            $footer->themeFile('footer.php');
            Tempstore::save('theme_footer', $footer);

            $metaTag = new MetaTag();
            Tempstore::save('theme_meta_tags', $metaTag);

            if(!empty($params)) {
                $_GET = array_merge($_GET, $params);
            }

            // Found matched route info
            $routeBuilder = new RouteBuilder();
            $this->loadedRoute = $routeBuilder->getRouteByPattern($pattern);

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
                    // TODO: calling hooks.
                    // TODO: Writing response.

                    $this->response->send();
                    exit;
                }
            }

            // If reach at this point the dont have controller.
            throw new ControllerMissingException("Controller not found (".$controller. ")");
        }
        else {
            //TODO go 404 page.
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
        stream_wrapper_register('public', 'Mini\Cms\Modules\Streams\MiniWrapper', STREAM_IS_URL);
        stream_wrapper_register('private', 'Mini\Cms\Modules\Streams\MiniWrapper', STREAM_IS_URL);
    }
}