<?php

namespace Mini\Cms\Hooks;

class Hook
{

    public static function hookFiles(): array
    {
        if(is_dir('hooks')) {

            // Read directory files
            if($files = scandir("hooks")) {
                return array_diff($files, ['.', '..']);
            }
        }
        return [];
    }

}