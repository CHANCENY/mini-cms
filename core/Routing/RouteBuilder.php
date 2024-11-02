<?php

namespace Mini\Cms\Routing;

use Mini\Cms\Controller\ControllerInterface;
use Mini\Cms\Controller\Request;
use Mini\Cms\Controller\Response;
use Mini\Cms\Modules\Extensions\Extensions;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Yaml\Yaml;

class RouteBuilder
{
    private array $new_route;
    /**
     * @var array|mixed
     */
    private array $routes;


    public function __construct()
    {
        $this->new_route = [];
        $this->new_route['id'] = Uuid::uuid4()->toString();
        $this->new_route['options']['unauthorized_access'] = false;
        $this->new_route['options']['methods'][] = 'GET';

        global $routes;

        $this->routes = [];

        if(empty($routes)) {
            if($module_routes = Extensions::importRoutes()) {
                $this->routes = array_merge($this->routes, $module_routes);
            }
        }
        else {
            $this->routes = $routes;
        }
    }

    /**
     * Getting all routes.
     * @return array
     */
    public function getRoutes(): array
    {
        return array_map(function ($route) {
            return new Route($route['id']);
        },$this->routes);
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

    public function getPatterns(): array
    {
        return array_map(function($item){
            return $item['url'];
        },$this->routes);
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