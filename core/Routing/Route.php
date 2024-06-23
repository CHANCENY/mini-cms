<?php

namespace Mini\Cms\Routing;

use Mini\Cms\Modules\Access\Role;
use Mini\Cms\Modules\Extensions\Extensions;
use Mini\Cms\Modules\Extensions\ModuleHandler\ModuleHandler;

class Route
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

    /**
     * Constructor to build up routes.
     */
    public function __construct(string $route_id)
    {
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
        $fullCollection = array_merge($this->routes, $this->defaults);
        $this->route = array_filter($fullCollection, function ($route) use ($route_id) {
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
        if(in_array('*', $this->getRoles())) {
            return true;
        }
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
}