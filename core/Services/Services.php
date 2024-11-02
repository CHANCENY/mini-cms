<?php

namespace Mini\Cms\Services;

use Mini\Cms\Modules\Extensions\Extensions;
use Mini\Cms\Modules\Storage\Tempstore;
use Mini\Cms\System\System;
use Mini\Cms\Theme\FileLoader;
use Symfony\Component\Yaml\Yaml;

class Services extends System implements ServiceInterface
{
    private array $services;
    /**
     * Construct loads declarations.
     */
    public function __construct()
    {
        parent::__construct();
        global $services;
        $this->services = $services;
    }

    /**
     * Creating service.
     * @param string $service_name
     * @return mixed
     * @throws \Exception
     */
    public static function create(string $service_name): mixed
    {
        $service = new Services();
        return $service->get($service_name);
    }

    /**
     * Getting a service object.
     * @param string $service_name
     * @return mixed|null
     * @throws \Exception
     */
    private function get(string $service_name): mixed
    {
        $found = $this->services[$service_name] ?? throw new \Exception("Service '$service_name' not found");
        return new $found();
    }
}