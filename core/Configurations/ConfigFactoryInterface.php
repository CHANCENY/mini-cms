<?php
declare(strict_types=1);

namespace Mini\Cms\Configurations;

interface ConfigFactoryInterface
{
    /**
     * Adding configuration.
     * @param string $name Name or Key of configuration.
     * @param array $value Value for given name or key.
     * @return void
     */
    public function set(string $name, array $value): void;

    /**
     * Getting configuration.
     * @param string $name Key or name of configuration.
     * @return array|null Value found.
     */
    public function get(string $name): ?array;

    /**
     * Save configuration.
     * @param bool $take_backup True before any change backup will be taken.
     * @return mixed
     */
    public function save(bool $take_backup = false): bool;

    /**
     * Removing configuration.
     * @param string $name Name or key of configuration to remove.
     * @return bool True if removed.
     */
    public function delete(string $name): bool;
}