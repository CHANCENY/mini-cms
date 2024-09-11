<?php

namespace Mini\Cms\Modules\PermanentStorage\Statement;

use Mini\Cms\Modules\PermanentStorage\Engine\Engine;
use Mini\Cms\Modules\PermanentStorage\Statement\interfaces\TransactionInterface;
use Mini\Cms\Modules\PermanentStorage\Statement\interfaces\TransactionStatementInterface;

class Transaction implements TransactionInterface
{

    /**
     * Collections presentations.
     * @var array
     */
    private array $transaction_data_storage;

    private string $condition_key;
    private mixed $condition_value;
    private string $operator;

    private array $delete_condition;

    private array $temporary_data;
    /**
     * @var true
     */
    private bool $validated;

    public function __construct(private Engine $engine)
    {
        $this->temporary_data = [];
        $this->condition_value = '';
        $this->condition_key = '';
        $this->validated = false;
        $this->operator = '=';
    }

    /**
     * @inheritDoc
     */
    public function delete(string $collection_name): bool
    {
        if($this->validated) {
            $all_content = $this->transaction_data_storage[$collection_name];
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
            $this->transaction_data_storage[$collection_name]['content'] = $all_content['content'];
            return true;
        }
        return false;
    }

    /**
     * @inheritDoc
     */
    public function setData(string $key, mixed $value): void
    {
        $this->temporary_data[$key] = $value;
    }

    /**
     * @inheritDoc
     */
    public function unset(string $key): bool
    {
        if(isset($this->temporary_datatemporary_data[$key])) {
            unset($this->temporary_datatemporary_data[$key]);
        }
        return empty($this->temporary_datatemporary_data[$key]);
    }

    /**
     * @inheritDoc
     */
    public function validate(string $collection_name): bool
    {
        if($this->condition_key && $this->condition_value && $this->operator) {

            $collection_keys = $this->engine->getCollectionKeys($collection_name);
            $collection_type = $this->engine->getCollectionTypes($collection_name);
            $update_data_keys = array_keys($this->temporary_data);

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

                if(isset($this->temporary_data[$collection_key])) {
                    $casted_value = $this->castingTo($casting_to, $this->temporary_data[$collection_key]);
                    if($casting_to === 'int' && is_int($casted_value)) {
                        $this->temporary_data[$collection_key] = $casted_value;
                    }
                    elseif($casting_to === 'float' && is_float($casted_value)) {
                        $this->temporary_data[$collection_key] = $casted_value;
                    }
                    elseif($casting_to === 'boolean' && is_bool($casted_value)) {
                        $this->temporary_data[$collection_key] = $casted_value;
                    }
                    elseif($casting_to === 'array' && is_array($casted_value)) {
                        $this->temporary_data[$collection_key] = $casted_value;
                    }
                    elseif($casting_to === 'datetime' && is_numeric($casted_value)) {
                        $this->temporary_data[$collection_key] = $casted_value;
                    }
                    elseif($casting_to === 'string' && is_string($casted_value)) {
                        $this->temporary_data[$collection_key] = $casted_value;
                    }
                }
            }

            $this->validated = true;
            return $this->validated;
        }
        else {
            $collection_keys = $this->engine->getCollectionKeys($collection_name);
            $collection_type = $this->engine->getCollectionTypes($collection_name);
            $unique = $this->engine->getCollectionUnique($collection_name);
            $primary = $this->engine->getCollectionPrimary($collection_name);

            if(count($collection_type) !== count($collection_keys)) {
                return false;
            }

            $insertion_keys = array_keys($this->temporary_data);

            if(count($insertion_keys) > count($collection_keys)) {
                return false;
            }

            foreach ($collection_keys as $collection_key) {
                $index = array_search($collection_key,$collection_keys);
                $casting_to = $collection_type[$index];
                $casted_value = $this->castingTo($casting_to, $this->temporary_data[$collection_key]);
                if($casting_to === 'int' && is_int($casted_value)) {
                    $this->temporary_data[$collection_key] = $casted_value;
                }
                elseif($casting_to === 'float' && is_float($casted_value)) {
                    $this->temporary_data[$collection_key] = $casted_value;
                }
                elseif($casting_to === 'boolean' && is_bool($casted_value)) {
                    $this->temporary_data[$collection_key] = $casted_value;
                }
                elseif($casting_to === 'array' && is_array($casted_value)) {
                    $this->temporary_data[$collection_key] = $casted_value;
                }
                elseif($casting_to === 'datetime' && is_numeric($casted_value)) {
                    $this->temporary_data[$collection_key] = $casted_value;
                }
                elseif($casting_to === 'string' && is_string($casted_value)) {
                    $this->temporary_data[$collection_key] = $casted_value;
                }
            }

            if(!$primary) {
                return false;
            }

            /**@var $select_interface \Mini\Cms\Modules\PermanentStorage\Statement\Select **/
            $select_interface = $this->engine->select;
            foreach ($primary as $item) {
                $old_data = $select_interface->get($collection_name,$item,$this->temporary_data[$item],'=');
                if(!empty($old_data)) {
                    throw new \Exception("Primary key '$item' can not have duplicate");
                }
            }
            foreach ($primary as $value) {
                if(isset($this->transaction_data_storage[$collection_name]['content'])) {
                    $found = array_filter($this->transaction_data_storage[$collection_name]['content'],function ($item) use ($value){
                        if(isset($this->temporary_data[$value]) && isset($item[$value]) && $item[$value] === $this->temporary_data[$value]) {
                            return $item;
                        }
                    });
                    if($reset = reset($found)){
                        throw new \Exception("Primary key '$value' can not have duplicate");
                    }
                }
            }

            if($unique) {
                foreach ($unique as $item) {
                    $old_data = $select_interface->get($collection_name,$item,$this->temporary_data[$item],'=');
                    if(!empty($old_data)) {
                        throw new \Exception("Unique key '$item' can not have duplicate");
                    }
                }

                foreach ($unique as $value) {
                    if(isset($this->transaction_data_storage[$collection_name]['content'])) {
                        $found = array_filter($this->transaction_data_storage[$collection_name]['content'],function ($item) use ($value){
                            if(isset($this->temporary_data[$value]) && isset($item[$value]) && $item[$value] === $this->temporary_data[$value]) {
                                return $item;
                            }
                        });
                        if($reset = reset($found)){
                            throw new \Exception("Unique key '$value' can not have duplicate");
                        }
                    }
                }
            }
            $this->validated = true;
            return true;
        }
    }

    /**
     * @inheritDoc
     */
    public function save(string $collection_name): bool
    {
        if($this->validated) {
            $this->transaction_data_storage[$collection_name]['content'][] = $this->temporary_data;
            return true;
        }
        return false;
    }

    /**
     * @inheritDoc
     */
    public function reset(): void
    {
        $this->validated = false;
    }

    public function startTransaction(array $collections_names): void
    {
        foreach ($collections_names as $collection_name) {
            $this->transaction_data_storage[$collection_name] = $this->engine->readCollectionFile($collection_name);
        }
    }

    public function commit(): bool
    {
        foreach ($this->transaction_data_storage as $key=>$data) {
            $this->engine->writeCollectionFile($key, $data);
        }
        return true;
    }

    public function rollback(): void
    {
        foreach ($this->transaction_data_storage as $key=>$value) {
            $this->transaction_data_storage[$key] = $this->engine->readCollectionFile($key);
        }
    }

    public function getTransaction(): TransactionStatementInterface
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setCondition(string $key, mixed $value, string $operator = '='): void
    {
        $this->condition_key = $key;
        $this->condition_value = $value;
        $this->operator = $operator;
        $this->delete_condition[$key] = $value;
    }

    /**
     * @inheritDoc
     */
    public function update(string $collection_name): bool
    {
        if($this->validated) {
            $casting_to = $this->validateKey($collection_name, $this->condition_key);
            $condition_value = $this->castingTo($casting_to,$this->condition_value);
            if(!empty($this->transaction_data_storage[$collection_name]['content'])) {
                foreach ($this->transaction_data_storage[$collection_name]['content'] as $key=>$item) {
                    if(isset($item[$this->condition_key])) {
                        if($this->operator === '=' && $item[$this->condition_key] === $condition_value) {
                            $this->transaction_data_storage[$collection_name]['content'][$key] = $this->itemUpdate($item);
                        }
                        elseif ($this->operator === '!=' && $item[$this->condition_key] !== $condition_value) {
                            $this->transaction_data_storage[$collection_name]['content'][$key] = $this->itemUpdate($item);
                        }
                        elseif ($this->operator === '<=' && $item[$this->condition_key] <= $condition_value) {
                            $this->transaction_data_storage[$collection_name]['content'][$key] = $this->itemUpdate($item);
                        }
                        elseif ($this->operator === '>=' && $item[$this->condition_key] >= $condition_value) {
                            $this->transaction_data_storage[$collection_name]['content'][$key] = $this->itemUpdate($item);
                        }
                    }
                }
            }
            return true;
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

    private function itemUpdate(mixed $item)
    {
        foreach ($this->temporary_data as $key=>$value) {
            $item[$key] = $value ?? $item[$key];
        }
        return $item;
    }
}