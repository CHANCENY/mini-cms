<?php

namespace Mini\Cms\Modules\PermanentStorage\Statement\interfaces;

interface UpdateInterface
{
    /**
     * Data to be used for updated.
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function setData(string $key, mixed $value): void;

    /**
     * Validate given data.
     * @param string $collection_name
     * @return bool
     */
    public function validate(string $collection_name):bool;

    /**
     * Set condition for updating.
     * @param string $key
     * @param mixed $value
     * @param string $operator
     * @return void
     */
    public function setCondition(string $key, mixed $value, string $operator = '='): void;

    /**
     * Update the database.
     * @param string $collection_name
     * @return bool
     */
    public function update(string $collection_name): bool;
}