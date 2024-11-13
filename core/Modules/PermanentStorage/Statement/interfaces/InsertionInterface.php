<?php

namespace Mini\Cms\Modules\PermanentStorage\Statement\interfaces;

interface InsertionInterface
{
    /**
     * Add data to the new created object for insertion.
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function setData(string $key, mixed $value): void;

    /**
     * Remove key from insertion data.
     * @param string $key
     * @return bool
     */
    public function unset(string $key): bool;

    /**
     * Validate data before insertion.
     * @param string $collection_name
     * @return bool
     */
    public function validate(string $collection_name): bool;

    /**
     * Persist new insertion data to collection.
     * @param string $collection_name
     * @return bool
     */
    public function save(string $collection_name): bool;

    /**
     * Reset validated lock.
     * @return void
     */
    public function reset(): void;
}