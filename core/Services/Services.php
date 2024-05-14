<?php

namespace Mini\Cms\Services;

class Services implements ServiceInterface
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
    private string $servicePath = '../configs/services.json';

    /**
     * Construct loads declarations.
     */
    public function __construct()
    {
        if(isset($this->servicePath) && file_exists($this->servicePath)) {
            $this->services = json_decode(file_get_contents($this->servicePath), true);
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