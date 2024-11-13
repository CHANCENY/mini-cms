<?php

namespace Mini\Cms\Modules\PermanentStorage\Statement;

use Mini\Cms\Modules\PermanentStorage\Engine\Engine;
use Mini\Cms\Modules\PermanentStorage\Statement\interfaces\DeleteInterface;

class Delete implements DeleteInterface
{

    private array $delete_condition;

    private bool $validated;

    public function __construct(private Engine $engine)
    {
        $this->validated = false;
    }

    /**
     * @inheritDoc
     */
    public function setCondition(string $key, mixed $value): void
    {
       $this->delete_condition[$key] = $value;
    }

    /**
     * @inheritDoc
     */
    public function validate(string $collection_name): bool
    {
        $collection_keys = $this->engine->getCollectionKeys($collection_name);
        $collection_type = $this->engine->getCollectionTypes($collection_name);
        $delete_condition_key = array_keys($this->delete_condition);

        if(count($collection_type) !== count($collection_keys)) {
            return false;
        }
        foreach ($delete_condition_key as $item) {
            if(!in_array($item, $collection_keys)) {
                return false;
            }
        }
        foreach ($collection_keys as $collection_key) {
            $index = array_search($collection_key,$collection_keys);
            $casting_to = $collection_type[$index];

            if(isset($this->delete_condition[$collection_key])) {
                $casted_value = $this->castingTo($casting_to, $this->delete_condition[$collection_key]);
                if($casting_to === 'int' && is_int($casted_value)) {
                    $this->delete_condition[$collection_key] = $casted_value;
                }
                elseif($casting_to === 'float' && is_float($casted_value)) {
                    $this->delete_condition[$collection_key] = $casted_value;
                }
                elseif($casting_to === 'boolean' && is_bool($casted_value)) {
                    $this->delete_condition[$collection_key] = $casted_value;
                }
                elseif($casting_to === 'array' && is_array($casted_value)) {
                    $this->delete_condition[$collection_key] = $casted_value;
                }
                elseif($casting_to === 'datetime' && is_numeric($casted_value)) {
                    $this->delete_condition[$collection_key] = $casted_value;
                }
                elseif($casting_to === 'string' && is_string($casted_value)) {
                    $this->delete_condition[$collection_key] = $casted_value;
                }
            }
        }
        $this->validated = true;
        return $this->validated;
    }

    /**
     * @inheritDoc
     */
    public function delete(string $collection_name): bool
    {
        if($this->validated) {
            $all_content = $this->engine->readCollectionFile($collection_name);
            $keys = array_keys($this->delete_condition);
            $condition_key = $keys[0];
            $copy_data = [];
            $casting_to = $this->validateKey($collection_name, $condition_key);
            $condition_value = $this->castingTo($casting_to,$this->delete_condition[$condition_key]);
            if(!empty($all_content['content'])) {
                foreach ($all_content['content'] as $key=>$item) {
                    if(isset($item[$condition_key])) {
                        if($item[$condition_key] !== $condition_value) {
                            $copy_data[] = $item;
                        }
                    }
                }
                $all_content['content'] = $copy_data;
            }
            return $this->engine->writeCollectionFile($collection_name, $all_content);
        }
        return false;
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


    private function validateKey($collection_name, $key): string
    {
        $collection_keys = $this->engine->getCollectionKeys($collection_name);
        $collection_types = $this->engine->getCollectionTypes($collection_name);

        $index = array_search($key,$collection_keys);
        if($index === false) {
            throw new \Exception("Key '$key' does not exist in collection");
        }
        return $collection_types[$index];
    }
}