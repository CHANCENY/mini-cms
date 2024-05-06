<?php
declare(strict_types=1);

namespace Mini\Cms\Configurations;

class ConfigFactory implements ConfigFactoryInterface
{

    /**
     * Configuration loaded in system.
     * @var array
     */
    private array $configurationObject;

    /**
     * Where configurations are stored.
     * @var string
     */
    private string $configurationPath = "../configs/configurations.json";

    /**
     * Loading configurations.
     */
    public function __construct()
    {
        if(isset($this->configurationPath) && file_exists($this->configurationPath)) {
            $this->configurationObject = json_decode(file_get_contents($this->configurationPath), true) ?? [];
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
            $backup = "../configs/backups/configurations.json";
            copy($this->configurationPath, $backup);
        }
        return !empty(file_put_contents($this->configurationPath, json_encode($this->configurationObject, JSON_PRETTY_PRINT
        )));
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
}