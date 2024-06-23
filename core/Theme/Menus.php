<?php

namespace Mini\Cms\Theme;


use Mini\Cms\Configurations\ConfigFactory;
use Mini\Cms\Modules\CurrentUser\CurrentUser;
use Mini\Cms\Routing\URIMatcher;
use Mini\Cms\Services\Services;

class Menus
{
    private array $menus;
    private string $current_uri;

    private string $default_menu = '../core/default/menu.json';

    private string $custom_menu = '../configs/menus.json';

    private array $default_menus;

    private array $custom_menus;

    private array $active;

    /**
     * Construct loads menus register in menu_register.json
     * @param string $current_uri
     */
    public function __construct(string $current_uri = '/')
    {
        if(file_exists($this->default_menu)) {
            $this->default_menus = json_decode(file_get_contents($this->default_menu), true) ?? [];
        }
        if(file_exists($this->custom_menu)) {
            $this->custom_menus = json_decode(file_get_contents($this->custom_menu) ?? '{}', true) ?? [];
        }

        $config = Services::create('config.factory');
        $currentUser = Services::create('current.user');

        // Remove unauthorized menus
        foreach ($this->default_menus as $k=>$menu) {

           if(!empty($menu['options']['roles'])) {

               if($currentUser instanceof CurrentUser) {

                   $roles = $menu['options']['roles'];
                   if(!in_array('*', $roles)) {

                       if(!array_intersect($roles,$currentUser->getRoles()))
                       {
                           unset($this->default_menus[$k]);
                       }
                   }
               }

               if($config instanceof ConfigFactory) {
                   $database = $config->get('database');
                   if(!empty($database) && $k === 'menu_installation') {
                       unset($this->default_menus[$k]);
                   }
               }
           }

            if(!empty($menu['options']['permission'])) {
                $permissions = $menu['options']['permission'];
                if($currentUser instanceof CurrentUser) {

                    $flag = false;
                    if(in_array('administrator_access', $permissions)) {
                        if(!$currentUser->isAdmin()) {
                            $flag = true;
                        }
                    }

                    if(in_array('authenticated_access', $permissions)) {
                        if(!$currentUser->isAuthenticated()) {
                            $flag = true;
                        }
                    }

                    if($flag) {
                        if(!in_array('anonymous_access', $permissions)) {
                            unset($this->default_menus[$k]);
                        }
                    }

                    if(count($permissions)  === 1 && $permissions[0] === 'anonymous_access') {

                        if(!empty($currentUser->id())) {
                            unset($this->default_menus[$k]);
                        }
                    }
                }
            }

        }
        foreach ($this->custom_menus as $k=>$menu) {

            if(!empty($menu['options']['roles'])) {

                if($currentUser instanceof CurrentUser) {

                    $roles = $menu['options']['roles'];
                    if(!in_array('*', $roles)) {

                        if(!array_intersect($roles,$currentUser->getRoles()))
                        {
                            unset($this->custom_menus[$k]);
                        }
                    }
                }

                if($config instanceof ConfigFactory) {
                    $database = $config->get('database');
                    if(!empty($database) && $k === 'menu_installation') {
                        unset($this->custom_menus[$k]);
                    }
                }
            }

            if(!empty($menu['options']['permission'])) {
                $permissions = $menu['options']['permission'];
                if($currentUser instanceof CurrentUser) {

                    $flag = false;
                    if(in_array('administrator_access', $permissions)) {
                        if(!$currentUser->isAdmin()) {
                            $flag = true;
                        }
                    }

                    if(in_array('authenticated_access', $permissions)) {
                        if(!$currentUser->isAuthenticated()) {
                            $flag = true;
                        }else {
                            $flag = false;
                        }
                    }

                    if($flag) {
                        //dump($flag, $permissions,$menu);
                        if(!in_array('anonymous_access', $permissions)) {
                            unset($this->custom_menus[$k]);
                        }
                    }

                    if(count($permissions)  === 1 && $permissions[0] === 'anonymous_access') {

                        if(!empty($currentUser->id())) {
                            unset($this->custom_menus[$k]);
                        }
                    }
                }
            }

        }

        // TODO: check if current user is admin
        $this->menus = array_merge($this->default_menus, $this->custom_menus);
        $this->current_uri = $current_uri;
        // Bring these menus from hook menu_register.

        // Find active.
        foreach ($this->menus as $key=>$menu) {

            $matcher = new URIMatcher([$menu['link']]);
            if($matcher->matchCurrentURI($this->current_uri)) {
                $this->menus[$key]['options']['active'] = true;
            }
            if(!empty($menu['children'])) {
                $this->traverChildrenMenusToMarkActive($this->menus[$key]['children']);
            }
        }
        foreach ($this->menus as $key=>$menu) {
            $this->menus[$key] = new Menu($menu);
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
}