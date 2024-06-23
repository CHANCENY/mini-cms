<?php

namespace Mini\Cms\Modules\Modal;

use Mini\Cms\Entities\User;

readonly class RecordCollection
{

    /**
     * @param mixed $record
     */
    public function __construct(private array $record)
    {
    }

    /**
     * Get field value
     * @param string $name field/column name.
     * @param array $arguments
     * @return int|null|string|array|bool|double
     */
    public function __call(string $name, array $arguments)
    {
        // Check if we have key called.
        if(isset($this->record[$name])) {
            return $this->record[$name];
        }
        return null;
    }

    /**
     * Getting column value.
     * @param string $name column name.
     * @return int|null|string|array|bool|double
     */
    public function __get(string $name)
    {
       if(isset($this->record[$name])) {
           return $this->record[$name];
       }
       return null;
    }

    /**
     * Json data of record.
     * @return string
     */
    public function __toString(): string
    {
       return json_encode($this->record);
    }
}