<?php

namespace Mini\Cms\Theme;

use Mini\Cms\Modules\Storage\Tempstore;

class Render
{
    public function render($template, $data = []) {
        return Tempstore::load('theme_loaded')->view($template, $data);
    }
}