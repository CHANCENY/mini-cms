<?php

namespace Mini\Cms\Theme;

class Menu
{
    private array $menu;

    public function __construct(array $menu)
    {
        $this->menu = $menu;
    }

    public function getLabel(): string
    {
        return $this->menu['label'] ?? '';
    }

    public function getLink(): string
    {
        return $this->menu['link'] ?? '';
    }

    public function getIcon(): string
    {
        return $this->menu['icon'] ?? '';
    }

    public function getChildren(): array
    {
        return array_map(function ($item){
            return new Menu($item);
        },$this->menu['children'] ?? []);
    }

    public function getAttributes(): array
    {
        return $this->menu['attributes'] ?? [];
    }

    public function getOptions(): array
    {
        return $this->menu['options'] ?? [];
    }
}