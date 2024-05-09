<?php

namespace Mini\Cms\Routing;

use Mini\Cms\Controller\ControllerInterface;
use Mini\Cms\Controller\Request;
use Mini\Cms\Controller\Response;
use Ramsey\Uuid\Uuid;

class RouteBuilder
{
    /**
     * Path to defaults routes.
     * @var string
     */
    private string $default_routes = '../core/default/default_routes.json';

    /**
     * Path to custom routes.
     * @var string
     */
    private string $custom_routes = '../configs/custom_routes.json';

    private array $new_route;
    /**
     * @var array|mixed
     */
    private array $routes;
    /**
     * @var array|mixed
     */
    private mixed $defaults;

    public function __construct()
    {
        $this->new_route = [];
        $this->new_route['id'] = Uuid::uuid4()->toString();
        $this->new_route['options']['unauthorized_access'] = false;
        $this->new_route['options']['methods'][] = 'GET';

        $this->routes = [];
        $this->defaults = [];

        if(file_exists($this->default_routes)) {
            $this->defaults = json_decode(file_get_contents($this->default_routes), true) ?? [];
        }
        else {
            $alternative_path ='../default/default_routes.json';
            if(file_exists($alternative_path)) {
                $this->defaults = json_decode(file_get_contents($alternative_path), true) ?? [];
            }
        }

        if(file_exists($this->custom_routes)) {
            $this->routes = json_decode(file_get_contents($this->custom_routes), true) ?? [];
        }
        else {
            $alternative_path = '../../configs/custom_routes.json';
            if(file_exists($alternative_path)) {
                $this->routes = json_decode(file_get_contents($alternative_path), true) ?? [];
            }
        }
    }

    /**
     * @param string $new_route_uri Uri to access controller eg /users or /user/{uid} or /user/{uid}/edit
     * @return bool
     * @throws \Exception
     */
    public function setNewUrl(string $new_route_uri): bool
    {
        $registeredRoute = $this->getRoutes();
        $flag = false;
        array_filter($registeredRoute, function ($route) use ($new_route_uri, &$flag) {
            if($route instanceof Route) {
                $oldUrl = $route->getUrl();
                if($oldUrl === $new_route_uri) {
                    $flag = true;
                }
            }
        });
        if(!$flag) {
            $this->new_route['url'] = $new_route_uri;
            return true;
        }
        throw new \Exception('URL provided already exist in system');
    }

    /**
     * @param string $description
     * @return void
     */
    public function setDescription(string $description): void
    {
        $this->new_route['description'] = $description;
    }

    /**
     * @param string $role
     * @return void
     */
    public function setAllowedRole(string $role): void
    {
        $this->new_route['options']['roles'][] = $role;
    }

    /**
     * @param bool $state true or false if True the route will be public.
     * @return void
     */
    public function setUnAuthorizedAccess(bool $state): void
    {
        $this->new_route['options']['unauthorized_access'] = $state;
    }

    /**
     * @param string $method
     * @return void
     */
    public function setMethod(string $method): void
    {
        $this->new_route['options']['methods'][] = strtoupper($method);
    }

    /**
     * The handle must implement ControllerInterface.
     * @param string $handler This is class that will be handling the request on this route.
     * @return void
     * @throws \Exception
     */
    public function setHandler(string $handler): void
    {
        if(class_exists($handler) && (new $handler(new Request(), new Response())) instanceof ControllerInterface) {
            $this->new_route['handler'] = $handler;
        }
        else {
            throw new \Exception('Handler not found');
        }
    }

    /**
     * @param string $name
     * @return void
     */
    public function setName(string $name): void
    {
        $this->new_route['name'] = $name;
    }

    /**
     * Getting all routes.
     * @return array
     */
    public function getRoutes(): array
    {
        return array_map(function ($route) {
            return new Route($route['id']);
        },array_merge($this->routes, $this->defaults));
    }

    /**
     * Getting only default routes.
     * @return array
     */
    public function getDefaultRoutes(): array
    {
        return array_map(function ($route) {
            return new Route($route['id']);
        },$this->defaults);
    }

    /**
     * Getting custom routes.
     * @return array
     */
    public function getCustomRoutes(): array
    {
        return array_map(function ($route) {
            return new Route($route['id']);
        },$this->routes);
    }

    /**
     * Saving new route.
     * @param bool $is_default
     * @return bool
     */
    public function save(bool $is_default = false): bool
    {
        if($is_default === false) {
            $this->routes[] = $this->new_route;
            $alternative_path = '../../configs/custom_routes.json';
            if(file_exists($this->custom_routes)) {
                if(file_put_contents($this->custom_routes, json_encode($this->routes , JSON_PRETTY_PRINT))) {
                    return true;
                }
            }
            else {
                if(file_exists($alternative_path)) {
                    if(file_put_contents($alternative_path, json_encode($this->routes , JSON_PRETTY_PRINT))) {
                        return true;
                    }
                }
            }
        }
        else {
            $this->defaults[] = $this->new_route;
            $alternative_path  = '../default/default_routes.json';
            if(file_exists($this->default_routes)) {
                if(file_put_contents($this->default_routes, json_encode($this->defaults , JSON_PRETTY_PRINT))) {
                    return true;
                }
            }
            else {
                if(file_exists($alternative_path)) {
                    if(file_put_contents($alternative_path, json_encode($this->defaults , JSON_PRETTY_PRINT))) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    public function getPatterns(): array
    {
        return array_map(function($item){
            return $item['url'];
        }, array_merge($this->routes, $this->defaults));
    }

    public function getRouteByPattern(string $pattern): Route|false
    {
        $patternFound = array_filter(array_filter($this->getRoutes(),function($item) use ($pattern){
            if($item instanceof Route && $pattern === $item->getUrl()) {
                return $item;
            }
            return false;
        }));
        return reset($patternFound);
    }

}