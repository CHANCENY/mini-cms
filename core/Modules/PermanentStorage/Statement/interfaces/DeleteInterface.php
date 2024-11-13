<?php

namespace Mini\Cms\Modules\PermanentStorage\Statement\interfaces;

interface DeleteInterface
{

    /**
     * Give condition
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function setCondition(string $key, mixed $value): void;

    /**
     * Validate the conditions data.
     * @param string $collection_name
     * @return bool
     */
    public function validate(string $collection_name): bool;

    /**
     * Delete item.
     * @param string $collection_name
     * @return bool
     */
    public function delete(string $collection_name): bool;
}