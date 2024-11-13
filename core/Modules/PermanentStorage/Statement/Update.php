<?php

namespace Mini\Cms\Modules\PermanentStorage\Statement;

use Mini\Cms\Modules\PermanentStorage\Engine\Engine;
use Mini\Cms\Modules\PermanentStorage\Statement\interfaces\UpdateInterface;

class Update implements UpdateInterface
{

    private Engine $engine;

    private array $update_data;

    private string $condition_key;

    private string $condition_value;

    private string $operator = '=';
    /**
     * @var true
     */
    private bool $validated;

    public function __construct(Engine $engine)
    {
        $this->engine = $engine;
        $this->validated = false;
    }

    /**
     * @inheritDoc
     */
    public function setData(string $key, mixed $value): void
    {
        $this->update_data[$key] = $value;
    }

    /**
     * @inheritDoc
     */
    public function validate(string $collection_name): bool
    {
       if($this->condition_key && $this->condition_value && $this->operator) {

           $collection_keys = $this->engine->getCollectionKeys($collection_name);
           $collection_type = $this->engine->getCollectionTypes($collection_name);
           $update_data_keys = array_keys($this->update_data);

           if(count($collection_type) !== count($collection_keys)) {
               return false;
           }

           if(!in_array($this->condition_key,$collection_keys)) {
               return false;
           }

           foreach ($update_data_keys as $update_data_key) {
               if(!in_array($update_data_key, $collection_keys)) {
                   return false;
               }
           }

           foreach ($collection_keys as $collection_key) {
               $index = array_search($collection_key,$collection_keys);
               $casting_to = $collection_type[$index];

               if(isset($this->update_data[$collection_key])) {
                   $casted_value = $this->castingTo($casting_to, $this->update_data[$collection_key]);
                   if($casting_to === 'int' && is_int($casted_value)) {
                       $this->update_data[$collection_key] = $casted_value;
                   }
                   elseif($casting_to === 'float' && is_float($casted_value)) {
                       $this->update_data[$collection_key] = $casted_value;
                   }
                   elseif($casting_to === 'boolean' && is_bool($casted_value)) {
                       $this->update_data[$collection_key] = $casted_value;
                   }
                   elseif($casting_to === 'array' && is_array($casted_value)) {
                       $this->update_data[$collection_key] = $casted_value;
                   }
                   elseif($casting_to === 'datetime' && is_numeric($casted_value)) {
                       $this->update_data[$collection_key] = $casted_value;
                   }
                   elseif($casting_to === 'string' && is_string($casted_value)) {
                       $this->update_data[$collection_key] = $casted_value;
                   }
                   else {
                       throw new \Exception("Unsupported cast type detected '$casting_to'");
                   }
               }
           }

           $this->validated = true;
           return $this->validated;
       }
       return false;
    }

    /**
     * @inheritDoc
     */
    public function setCondition(string $key, mixed $value, string $operator = '='): void
    {
        $this->condition_key = $key;
        $this->condition_value = $value;
        $this->operator = $operator;
    }

    public function update(string $collection_name): bool
    {
        if($this->validated) {
            $all_content = $this->engine->readCollectionFile($collection_name);
            $casting_to = $this->validateKey($collection_name, $this->condition_key);
            $condition_value = $this->castingTo($casting_to,$this->condition_value);
            if(!empty($all_content['content'])) {
                foreach ($all_content['content'] as $key=>$item) {
                    if(isset($item[$this->condition_key])) {
                        if($this->operator === '=' && $item[$this->condition_key] === $condition_value) {
                            $all_content['content'][$key] = $this->itemUpdate($item);
                        }
                        elseif ($this->operator === '!=' && $item[$this->condition_key] !== $condition_value) {
                            $all_content['content'][$key] = $this->itemUpdate($item);
                        }
                        elseif ($this->operator === '<=' && $item[$this->condition_key] <= $condition_value) {
                            $all_content['content'][$key] = $this->itemUpdate($item);
                        }
                        elseif ($this->operator === '>=' && $item[$this->condition_key] >= $condition_value) {
                            $all_content['content'][$key] = $this->itemUpdate($item);
                        }
                    }
                }
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

    private function itemUpdate(mixed $item)
    {
        foreach ($this->update_data as $key=>$value) {
            $item[$key] = $value ?? $item[$key];
        }
        return $item;
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