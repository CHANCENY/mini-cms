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
        $this->bootServices();
    }

    /**
     * Boot up services once.
     * @return void
     * @throws ServiceProvideClassNotFound
     */
    private function bootServices(): void
    {

        foreach ($this->services as $service_name=>$service) {
            if(class_exists($service)) {
                $service = new $service();
                $this->services[$service_name] = $service;
            }
            else {
                throw new ServiceProvideClassNotFound($service_name .' services has invalid class');
            }
        }
    }

    /**
     * Creating service.
     * @param string $service_name
     * @return mixed
     */
    public static function create(string $service_name)
    {
        $service = new Services();
        return $service->get($service_name);
    }

    /**
     * Getting service object.
     * @param string $service_name
     * @return mixed|null
     */
    private function get(string $service_name)
    {
        return is_object($this->services[$service_name]) ? $this->services[$service_name] : null;
    }
}