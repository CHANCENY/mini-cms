<?php
declare(strict_types=1);

namespace Mini\Cms\Configurations;

use Mini\Cms\Modules\Cache\Caching;
use Mini\Cms\System\System;
use Mini\Cms\Theme\FileLoader;
use Symfony\Component\Yaml\Yaml;

class ConfigFactory extends System implements ConfigFactoryInterface
{

    /**
     * Configuration loaded in a system.
     * @var array
     */
    private array $configurationObject;

    /**
     * Where configurations are stored.
     * @var string
     */
    private string $configurationPath;

    /**
     * Loading configurations.
     */
    public function __construct()
    {
        global $configurations;
        parent::__construct();
        $this->configurationPath = (new FileLoader($this->getAppConfigRoot()))->findFiles('configurations.yml')[0] ?? null;
        if(isset($this->configurationPath) && file_exists($this->configurationPath) && empty($configurations)) {
            $this->configurationObject = Yaml::parseFile($this->configurationPath) ?? [];
            $configurations = $this->configurationObject;
            Caching::cache()->set('system_configurations', $this->configurationObject);
        }
        else {
            $this->configurationObject = $configurations;
        }
    }

    /**
     * @inheritDoc
     */
    public function set(string $name, array $value): void
    {
        $this->configurationObject[$this->removeSpecialCharacters($name)] = $value;
    }

    /**
     * @inheritDoc
     */
    public function get(string $name): ?array
    {
        return $this->configurationObject[$this->removeSpecialCharacters($name)] ?? [];
    }

    /**
     * @inheritDoc
     */
    public function save(bool $take_backup = false): bool
    {
        if($take_backup) {
            $backup = (new FileLoader($this->getAppConfigRoot()))->findFiles('backup_configurations.yml')[0] ?? null;
            if(!empty($backup)) {
                copy($this->configurationPath, $backup);
            }
        }
        $data = Yaml::dump($this->configurationObject);
        return !empty(file_put_contents($this->configurationPath, $data));
    }

    /**
     * @inheritDoc
     */
    public function delete(string $name): bool
    {
        if(isset($this->configurationObject[$this->removeSpecialCharacters($name)])) {
            unset($this->configurationObject[$this->removeSpecialCharacters($name)]);
            $this->save(true);
        }
        return !isset($this->configurationObject[$this->removeSpecialCharacters($name)]);
    }

    private function removeSpecialCharacters(string $name): string
    {
        return preg_replace('/[^A-Za-z0-9\-]/','_',$name);
    }

    /**
     * Configuration data.
     * @return array
     */
    public function getConfigurations(): array
    {
        return $this->configurationObject;
    }
}