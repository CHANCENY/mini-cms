<?php

namespace Mini\Cms\Modules\Extensions\ModuleHandler;

use Mini\Cms\Connections\Database\Database;
use Mini\Cms\Mini;
use Mini\Cms\Modules\Cache\Caching;
use Mini\Cms\Modules\Streams\MiniWrapper;
use Mini\Cms\Routing\Route;
use Symfony\Component\Yaml\Yaml;

/**
 *
 */
class ModuleHandler
{
    /**
     * @var array|mixed
     */
    private array $module;

    /**
     * @param int|string $module_id
     */
    public function __construct(int|string $module_id)
    {
        if(is_int($module_id)) {
            $this->module = Database::database()->query("SELECT * FROM `extensions` WHERE `ext_id` = {$module_id}")->fetch();
        }
        else {
            $this->module = Database::database()->query("SELECT * FROM `extensions` WHERE `ext_name` = '$module_id'")->fetch();
        }
    }

    public function id()
    {
        return $this->module['ext_id'];
    }

    /**
     * Get the name of module.
     * @return string|null
     */
    public function getName(): string|null
    {
        return $this->module['ext_name'] ?? null;
    }

    /**
     * Get a version of module.
     * @return string|null
     */
    public function getVersion(): string|null
    {
        return $this->module['ext_version'] ?? null;
    }

    /**
     * Get status of module.
     * @return bool
     */
    public function getStatus(): bool
    {
        return  !empty($this->module['ext_status']) && $this->module['ext_status'] === 'on';
    }

    /**
     * Get a path of module.
     * @return string|null
     */
    public function getPath(): string|null
    {
        return $this->module['ext_path'] ?? null;
    }

    /**
     * Get .module a file path
     * @return string|null
     */
    public function getHooksFile(): string|null
    {
        $path = trim($this->module['ext_path'], DIRECTORY_SEPARATOR) .DIRECTORY_SEPARATOR.$this->module['ext_name'].'.module';
        if(file_exists($path)){
            return (new MiniWrapper())->getRealPath($path);
        }
        return null;
    }

    public function getModuleRoutes(): array
    {
        $path = trim($this->module['ext_path'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR .$this->module['ext_name'].'.routing.yml';
        if(file_exists($path)){
            return Yaml::parseFile($path);
        }
        return [];
    }

    public function getServices(): array
    {
        $path = trim($this->module['ext_path'], DIRECTORY_SEPARATOR) .DIRECTORY_SEPARATOR.$this->module['ext_name'].'.services.api.yml';
        if(file_exists($path)){
           $services = Yaml::parseFile($path);
        }
        return [];
    }

    public function getMenus(): array
    {
        $path = trim($this->module['ext_path'], DIRECTORY_SEPARATOR) .DIRECTORY_SEPARATOR.$this->module['ext_name'].'.menus.yml';
        if(file_exists($path)){
            $menus = Yaml::parseFile($path);
            if(!empty($menus)) {
                return $menus;
            }
        }
        return [];
    }

    public function eTagRegisterRoutes(): bool
    {
        $routes = $this->getModuleRoutes();
        if(empty($routes)) {
            return false;
        }
        $etags = [];
        foreach ($routes as $route) {
            $route_obj = new Route($route['id']);
            if($route_obj->isUserAllowed(['anonymous']) && !(str_contains($route_obj->getUrl(), '{') || str_contains($route_obj->getUrl(), '}')) && $route_obj->isMethod('GET')) {
                $response = $route_obj->loadController();
                $data = $response->getBody();
                if(is_string($data)) {
                    $etags[$route_obj->getRouteId()] = [
                        'id' => md5($data),
                        'last_modified' => time()
                    ];
                }
            }
        }
        if(!empty($etags)) {
            Caching::cache()->set('etag-register', $etags);
            return true;
        }
        return false;
    }
}