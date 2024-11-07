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
        // Check if the service exists in the service array or can be instantiated directly
        $found = $this->services[$service_name] ?? null;
        if (is_null($found) && class_exists($service_name)) {
            $found = $service_name;
        }

        $reflection = new \ReflectionClass($found);
        // If there's no constructor, return a new instance directly
        if ($reflection->getConstructor() === null) {
            return $reflection->newInstance();
        }

        // Get constructor parameters
        $constructor = $reflection->getConstructor();
        $parameters = $constructor->getParameters();
        $dependencies = [];

        // Check if the class has a static 'create' method to provide default values
        $static_defaults = method_exists($found, 'create') ? $found::create() : [];

        foreach ($parameters as $parameter) {
            $dependency_class = $parameter->getType()?->getName();

            if ($dependency_class && class_exists($dependency_class)) {
                // Resolve class dependency recursively
                $dependencies[] = $this->get($dependency_class);
            } elseif (isset($static_defaults[$parameter->getName()])) {
                // Use value from static create method if available
                $dependencies[] = $static_defaults[$parameter->getName()];
            } elseif ($parameter->isDefaultValueAvailable()) {
                // Use default value if provided
                $dependencies[] = $parameter->getDefaultValue();
            } else {
                // Throw an exception if no way to resolve dependency
                throw new \Exception("Cannot resolve parameter '{$parameter->getName()}' for service '$service_name'");
            }
        }

        // Create the instance with resolved dependencies
        return $reflection->newInstanceArgs($dependencies);
    }


}