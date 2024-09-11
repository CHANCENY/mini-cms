<?php

namespace Mini\Cms\Modules\PermanentStorage\Statement\interfaces;

interface SelectInterface
{
    /**
     * Get all data in collection
     * @param string $collection_name
     * @return mixed
     */
    public function all(string $collection_name): mixed;

    /**
     * Get collection data by key.
     * NOTE: strict type checking is enforce.
     * @param string $collection_name
     * @param string $key Key in collection.
     * @param mixed $by matching value.
     * @param string $operator the operators can be =, !=, <=, >=
     * @return mixed
     */
    public function get(string $collection_name, string $key, mixed $by, string $operator): mixed;

    /**
     * Get data between min and max.
     * NOTE: strict type checking is enforce.
     * @param string $collection_name
     * @param string $key Key in collection.
     * @param int $min Minimum number.
     * @param int $max Maximum number
     * @return mixed
     */
    public function inRange(string $collection_name, string $key,int $min, int $max): mixed;

    /**
     * Get data from collection that key has value in by.
     * NOTE: strict type checking is enforce.
     * @param string $collection_name
     * @param string $key
     * @param array $by
     * @return mixed
     */
    public function inWithin(string $collection_name, string $key, array $by): mixed;

    /**
     * Get data from collection that key does not have value within
     * @param string $collection_name
     * @param string $key
     * @param array $by
     * @return mixed
     */
    public function notWithin(string $collection_name, string $key, array $by): mixed;

    /**
     * Get data by search
     * @param string $collection_name
     * @param string $key
     * @param string $by
     * @return mixed
     */
    public function includes(string $collection_name, string $key, string $by): mixed;

}