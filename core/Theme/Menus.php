<?php

namespace Mini\Cms\Theme;


use Mini\Cms\Routing\URIMatcher;

class Menus
{
    private array $menus;
    private string $current_uri;

    private array $active;

    /**
     * Construct loads menus register in menu_register.json
     * @param string $current_uri
     */
    public function __construct(string $current_uri = '/')
    {
        $this->current_uri = $current_uri;
        // Bring these menus from hook menu_register.
        $this->menus = [
            'menu_home' => [
                'label' => 'Home',
                'link' => '/home',
                'icon' => 'home',
                'children' => [
                    ['name' => 'Home child one',
                        'link' => '/home/child-1',
                        'icon' => 'home',
                        'attributes' => [
                            'class' => 'nav-item',
                            'id' => 'nav-item-1',
                        ],
                        'children' => []
                    ]
                ],
                'attributes' => [
                    'class' => 'nav-item',
                    'id' => 'nav-item-contact',
                    'title' => 'Home',
                ],
                'options' => [
                    'roles' => ['*'],
                ],
            ],
            'menu_contact_us' => [
                'label' => 'Contact Us',
                'link' => '/contact-us',
                'icon' => 'contact',
                'children' => [
                    ['name' => 'Contact child one',
                        'link' => '/contact-us/child-1',
                        'icon' => 'co',
                        'attributes' => [
                            'class' => 'nav-item',
                            'id' => 'nav-item-1',
                            'title' => 'Contact child one'
                        ],
                        'children' => []
                    ]
                ],
                'attributes' => [
                    'class' => 'nav-item',
                    'id' => 'nav-item-home',
                    'title' => 'Contact',
                ],
                'options' => [
                    'roles' => ['*'],
                ],
            ],
        ];

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
}