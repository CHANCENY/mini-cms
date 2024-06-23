<?php

namespace Mini\Cms\Services;

use Mini\Cms\Modules\Extensions\Extensions;
use Mini\Cms\Modules\Storage\Tempstore;
use Mini\Cms\System\System;
use Mini\Cms\Theme\FileLoader;

class Services extends System implements ServiceInterface
{

    /**
     * Services array.
     * @var array|mixed
     */
    private array $services;

    /**
     * Declarations file.
     * @var string
     */
    private string $servicePath;

    /**
     * Construct loads declarations.
     */
    public function __construct()
    {
        parent::__construct();

        $file = new FileLoader($this->getAppConfigRoot());
        $this->servicePath = $file->findFiles('services.json')[0] ?? null;

        $services = Tempstore::load('system.service');
        if(empty($services)) {
            if(isset($this->servicePath) && file_exists($this->servicePath)) {
                $this->services = json_decode(file_get_contents($this->servicePath), true);
                Tempstore::save('system.service', $this->services);
            }
        }
        else {
            $this->services = $services;
        }
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