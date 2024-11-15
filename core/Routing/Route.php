<?php

namespace Mini\Cms\Routing;

use Mini\Cms\Controller\Request;
use Mini\Cms\Controller\Response;
use Mini\Cms\Modules\Access\Role;
use Mini\Cms\Modules\Cache\Caching;
use Mini\Cms\Modules\Extensions\Extensions;
use Mini\Cms\Modules\Extensions\ModuleHandler\ModuleHandler;
use Symfony\Component\Yaml\Yaml;

class Route
{


    /**
     * Path to custom routes.
     * @var string
     */
    private string $custom_routes = '../configs/custom_routes.yml';

    /**
     * Custom routes.
     * @var array|mixed
     */
    private array $routes;

    /**
     * Default routes.
     * @var array|mixed
     */
    private array $defaults;

    /**
     * Route data.
     * @var array
     */
    private array $route;
    private array $options = [];

    /**
     * @param string $key
     * @param string|int|float $value
     */
    public function setOptions(string $key, string|int|float $value): void
    {
        $this->options[$key] = $value;
    }

    /**
     * Constructor to build up routes.
     */
    public function __construct(string $route_id)
    {
        global $routes;

        if(empty($routes)) {
            $this->routes = [];
            if($module_routes = Extensions::importRoutes()) {
                $this->routes = array_merge($this->routes, $module_routes);
            }
            Caching::cache()->set('system_routes', $this->routes);
        }else {
            $this->routes = $routes;
        }

        $this->route = array_filter($this->routes, function ($route) use ($route_id) {
            return $route['id'] === $route_id;
        });
        $this->route = reset($this->route);
    }

    public function getRoute(): array
    {
        return $this->route;
    }

    /**
     * Getting title of route.
     * @return mixed|null
     */
    public function getRouteTitle(): mixed
    {
        return $this->route['name'] ?? null;
    }

    /**
     * Getting description.
     * @return mixed|null
     */
    public function getRouteDescription(): mixed
    {
        return $this->route['description'] ?? null;
    }

    /**
     * Getting route id.
     * @return string
     */
    public function getRouteId(): string
    {
        return $this->route['id'];
    }

    /**
     * Get saved url.
     * @return string
     */
    public function getUrl(): string
    {
        return $this->route['url'];
    }

    /**
     * Route handlers.
     * @return mixed
     */
    public function getRouteHandler(): mixed
    {
        return $this->route['handler'];
    }

    /**
     * Allowed method.
     * @return array
     */
    public function getAllowedMethods(): array
    {
        return $this->route['options']['methods'] ?? [];
    }

    public function getOptions(): array
    {
        return $this->route['options'] ?? [];
    }

    public function getHeaders(): array
    {
        return $this->route['options']['headers'] ?? [];
    }

    public function getHeader(string $header): string
    {
        return $this->route['options']['headers'][$header] ?? '';
    }

    public function getPermission(): array
    {
        $roles = $this->getRoles();
        $permissions = [];
        foreach ($roles as $role) {
            $role = new Role($role);
            if($role->getName()) {
                $permissions[$role->getName()] = $role->getPermissions();
            }
        }
        return $permissions;
    }

    public function isAccessible()
    {
        return $this->route['options']['unauthorized_access'] ?? false;
    }

    public function getRoles()
    {
        return $this->route['options']['roles'] ?? ['anonymous'];
    }

    public function isMethod(string $method): bool
    {
        return in_array($method,$this->getAllowedMethods());
    }

    public function isUserAllowed(array $roles): bool
    {
        return !empty(array_intersect($roles, $this->getRoles()));
    }

    public function setRouteTitle(string $title): void
    {
       $this->route['name'] = $title;
    }

    public function setRouteDescription(string $description): void
    {
        $this->route['description'] = $description;
    }

    function replacePlaceholdersInUrl(array $options): string
    {
        $url = $this->getUrl();
        // Loop through the $params array and replace placeholders in the URL
        foreach ($options as $key => $value) {
            $url = str_replace('{' . $key . '}', $value, $url);
        }
        return $url;
    }

    function getRouteType(): string
    {
        return $this->route['controller_type'] ?? '_controller';
    }

    public function __toString()
    {
        return $this->replacePlaceholdersInUrl($this->options ?? []);
    }

    public function loadController(): ?Response
    {
        $controller = $this->getRouteHandler();
        $class_name = str_contains($controller, '::') ? explode('::', $controller)[0] : $controller;
        $request = Request::createFromGlobals();
        $response = new Response();
        $class_name_obj = new $class_name($request, $response);
        if($class_name_obj->isAccessAllowed()) {

            if(str_contains($controller, '::')) {
                $function = explode('::', $controller);
                $function = end($function);
                $class_name_obj->{$function}();
                return $response;
            }
            else {
                $function = 'writeBody';
                $class_name_obj->{$function}();
                return $response;
            }
        }
        return null;
    }
}