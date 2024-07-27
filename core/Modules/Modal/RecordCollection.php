<?php

namespace Mini\Cms\Modules\Modal;

use Mini\Cms\Entities\User;

class RecordCollection
{

    /**
     * @param mixed $record
     */
    public function __construct(private array $record)
    {
    }

    /**
     * This will just temporary hold data not persist to database.
     * @param string $column
     * @param mixed $value
     * @return void
     */
    public function set(string $column, mixed$value): void
    {
        if(!empty($value) && !is_array($value)) {
            $this->record[$column] = $value;
        }
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