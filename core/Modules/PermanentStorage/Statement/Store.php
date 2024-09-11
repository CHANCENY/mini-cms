<?php

namespace Mini\Cms\Modules\PermanentStorage\Statement;

use Mini\Cms\Modules\PermanentStorage\Engine\Engine;
use Mini\Cms\Modules\PermanentStorage\Statement\interfaces\InsertionInterface;

class Store implements InsertionInterface
{

    private array $insertion_data;

    private bool $validated = false;

    public function __construct(private Engine $engine)
    {
        $this->insertion_data = [];
    }

    /**
     * @inheritDoc
     */
    public function setData(string $key, mixed $value): void
    {
        if(!$this->validated) {
            $this->insertion_data[$key] = $value;
        }
    }

    /**
     * @inheritDoc
     */
    public function unset(string $key): bool
    {
        if(isset($this->insertion_data[$key])) {
            unset($this->insertion_data[$key]);
        }
        return empty($this->insertion_data[$key]);
    }

    public function validate(string $collection_name): bool
    {
        $collection_keys = $this->engine->getCollectionKeys($collection_name);
        $collection_type = $this->engine->getCollectionTypes($collection_name);
        $unique = $this->engine->getCollectionUnique($collection_name);
        $primary = $this->engine->getCollectionPrimary($collection_name);
        dump($collection_type, $collection_keys);
        if(count($collection_type) !== count($collection_keys)) {
            return false;
        }
        $insertion_keys = array_keys($this->insertion_data);
        if(count($insertion_keys) > count($collection_keys)) {
            return false;
        }

        foreach ($collection_keys as $collection_key) {
            $index = array_search($collection_key,$collection_keys);
            $casting_to = $collection_type[$index];
            $casted_value = $this->castingTo($casting_to, $this->insertion_data[$collection_key]);
            if($casting_to === 'int' && is_int($casted_value)) {
                $this->insertion_data[$collection_key] = $casted_value;
            }
            elseif($casting_to === 'float' && is_float($casted_value)) {
                $this->insertion_data[$collection_key] = $casted_value;
            }
            elseif($casting_to === 'boolean' && is_bool($casted_value)) {
                $this->insertion_data[$collection_key] = $casted_value;
            }
            elseif($casting_to === 'array' && is_array($casted_value)) {
                $this->insertion_data[$collection_key] = $casted_value;
            }
            elseif($casting_to === 'datetime' && is_numeric($casted_value)) {
                $this->insertion_data[$collection_key] = $casted_value;
            }
            elseif($casting_to === 'string' && is_string($casted_value)) {
                $this->insertion_data[$collection_key] = $casted_value;
            }
            else {
                throw new \Exception("Unsupported cast type detected '$casting_to'");
            }
        }
        if(!$primary) {
            return false;
        }

        /**@var $select_interface Select **/
        $select_interface = $this->engine->select;
        foreach ($primary as $item) {
            $old_data = $select_interface->get($collection_name,$item,$this->insertion_data[$item],'=');
            if(!empty($old_data)) {
                throw new \Exception("Primary key '$item' can not have duplicate");
            }
        }

        if($unique) {
            foreach ($unique as $item) {
                $old_data = $select_interface->get($collection_name,$item,$this->insertion_data[$item],'=');
                if(!empty($old_data)) {
                    throw new \Exception("Unique key '$item' can not have duplicate");
                }
            }
        }
        $this->validated = true;
        return true;
    }

    public function save(string $collection_name): bool
    {
        if($this->validated) {
            $all_content = $this->engine->readCollectionFile($collection_name);
            $all_content['content'][] = $this->insertion_data;
            return $this->engine->writeCollectionFile($collection_name, $all_content);
        }
        return false;
    }

    public function reset(): void
    {
        $this->validated = false;
    }

    private function castingTo(string $casting_to, mixed $by)
    {
        return match ($casting_to) {
            'int', 'datetime' => (int)$by,
            'string' => (string)$by,
            'boolean' => (bool)$by,
            'float' => (float)$by,
            'array' => (array)$by,
            default => $by,
        };
    }
}