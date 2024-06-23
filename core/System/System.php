<?php

namespace Mini\Cms\System;

use Mini\Cms\Modules\Extensions\Extensions;

class System
{
    /**
     * Root of project ie were core, configs and web reside.
     * @var string|null
     */
    private string|null $root;


    public function __construct()
    {
        $wrappers = [
            'public' => 'Mini\Cms\Modules\Streams\MiniWrapper',
            'private' => 'Mini\Cms\Modules\Streams\MiniWrapper',
            'module' => 'Mini\Cms\Modules\Streams\MiniWrapper',
            'theme' => 'Mini\Cms\Modules\Streams\MiniWrapper'
        ];
        foreach ($wrappers as $key=>$wrapper) {
            $registered_wrappers = stream_get_wrappers();
            if(!in_array($key,$registered_wrappers)) {
                stream_wrapper_register($key, $wrapper, STREAM_IS_URL);
            }
        }
        $this->root = null;

        // Lets get current working directory.
        $temp_dirs = __DIR__;

        // Initialize flag.
        $flag = 0;

        // Run through current by removing directory one by one until we get to root.
        while (true) {

            // Check if we are on root.
            if(is_dir($temp_dirs .DIRECTORY_SEPARATOR.'core')) {
                $this->root = $temp_dirs;
                break;
            }
            else {

                // Get path upto the last /
                $last_index =  strripos($temp_dirs, DIRECTORY_SEPARATOR);
                $temp_dirs = substr($temp_dirs,0, $last_index);
            }

            if($flag === 20) {
                break;
            }
            $flag++;
        }
    }

    /**
     * Getting project root.
     * @return string|null
     */
    public function getAppRoot(): ?string
    {
        return $this->root;
    }

    /**
     * Getting project web root.
     * @return string|null
     */
    public function getAppWebRoot(): ?string
    {
        return is_dir($this->root . DIRECTORY_SEPARATOR . 'web') ? $this->root . DIRECTORY_SEPARATOR . 'web' : null;
    }

    /**
     * Getting project configs root.
     * @return string|null
     */
    public function getAppConfigRoot(): ?string
    {
        return is_dir($this->root . DIRECTORY_SEPARATOR . 'configs') ? $this->root . DIRECTORY_SEPARATOR . 'configs' : null;
    }
}