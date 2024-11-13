<?php

namespace Mini\Cms\Theme;


use Mini\Cms\Modules\Extensions\Extensions;
use Mini\Cms\Modules\Extensions\ModuleHandler\ModuleHandler;
use Mini\Cms\Routing\Route;
use Mini\Cms\Services\Services;
use Mini\Cms\Routing\URIMatcher;
use Symfony\Component\Yaml\Yaml;
use Mini\Cms\Routing\RouteBuilder;
use Mini\Cms\Modules\Cache\Caching;
use Mini\Cms\Configurations\ConfigFactory;
use Mini\Cms\Modules\CurrentUser\CurrentUser;

class Menus
{
    private array $menus;

    private string $current_uri;

    /**
     * Construct loads menus register in menu_register.json
     * @param string $current_uri
     * @throws \Exception
     */
    public function __construct(string $current_uri = '/')
    {
        $this->current_uri = $current_uri;
        global $menus;
        if (empty($menus)) {
            $menus = [];
            $modules = Extensions::activeModules();
            foreach ($modules as $module) {
                if($module instanceof ModuleHandler) {
                   $menus = array_merge($menus, $module->getMenus());
                }
            }
            Caching::cache()->set("system_menus", $menus);
        }

        /**@var CurrentUser $currentUser **/
        $currentUser = Services::create('current.user');

        // TODO: check if current user is admin
        $this->menus = array_map(function($menu){ return new Menu($menu); },$menus);
        foreach ($this->menus as $key=>$menu) {
            if($menu instanceof Menu){
                $options = $menu->getOptions();
                $roles = $options['roles'] ?? [];
                $flag = false;
                foreach ($roles as $role) {
                    if(!$currentUser->isAdmin()) {
                        if(!in_array($role, $currentUser->getRoles())) {
                            $flag = true;
                        }
                    }
                    else {
                        if($role === 'anonymous') {
                            $flag = true;
                        }
                    }
                }
                if($flag) {
                    unset($this->menus[$key]);
                }
            }
        }
        $this->current_uri = $current_uri;
        // Bring these menus from hook menu_register.
    }

    protected function reloadMenus() {
        
        // Find active.
        foreach ($this->menus as $menu) {
            if($menu instanceof Menu) {
                $matcher = new URIMatcher([$menu->getLink()]);
                if ($matcher->matchCurrentURI($this->current_uri)) {
                    $menu->setActive();
                }
            }
            
        }
    }

    /**
     * Get all menus loaded.
     * @return array[]
     */
    public function getMenus(): array
    {
        return $this->menus;
    }

    /**
     * Set Menus item.
     * @param string $menu_id
     * @param array $menus
     * @param bool $on_runtime_only
     * @return void
     */
    public function setMenus(string $menu_id, array $menus, bool $on_runtime_only = true): void
    {
        $this->menus[$menu_id] = $menus;
        if($on_runtime_only) {
            //TODO save menus and reload.
        }
        $this->reloadMenus();
    }

    /**
     * Finding current uri to mark active.
     * @param array $menu
     * @return void
     */
    private function traverChildrenMenusToMarkActive(array &$menu): void
    {
        foreach ($menu as &$child) {

            $matcher = new URIMatcher([$child['link']]);
            if($matcher->matchCurrentURI($this->current_uri)) {
                $child['options']['active'] = true;
            }
            if(!empty($child['children'])) {
                $this->traverChildrenMenusToMarkActive($child['children']);
            }
        }
    }

    public function unset(string $menu_key): void
    {
        if(isset($this->menus[$menu_key])) {
            unset($this->menus[$menu_key]);
        }
    }

    /**
     * Set menu link using route id, not the route url should not contain patterns.
     * @param string $route_id
     * @return void
     */
    public function markRouteAsMenu(string $route_id): void
    {
        $routes = new RouteBuilder();
        $routes_collection = $routes->getRoutes();
        if($routes_collection) {
            $found = array_filter($routes_collection, function ($route) use ($route_id) {
               return $route->getRouteId() === $route_id;
            });

            if(!empty($found)) {
                $route = reset($found);
                if($route instanceof Route) {
                    $r = $route->getPermission();
                    $this->menus[$route_id] = new Menu([
                        'label' => $route->getRouteTitle(),
                        'link' => $route->getUrl(),
                        'options' => [
                            'roles' => $route->getRoles(),
                            'permissions' => reset( $r),
                        ],
                        'attributes' => [
                            "class"=> "nav-item",
                            "id"=> "nav-item-user",
                            "title"=> $route->getRouteTitle(),
                        ],
                        'children' => []
                    ]);
                }
            }
        }
        $this->reloadMenus();
    }
}