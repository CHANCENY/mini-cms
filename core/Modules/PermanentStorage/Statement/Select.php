<?php

namespace Mini\Cms\Modules\PermanentStorage\Statement;

use Mini\Cms\Modules\PermanentStorage\Engine\Engine;
use Mini\Cms\Modules\PermanentStorage\Statement\interfaces\SelectInterface;

readonly class Select implements SelectInterface
{
    public function __construct(private Engine $engine)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function all(string $collection_name): mixed
    {
        return $this->engine->readCollectionFile($collection_name)['content'] ?? false;
    }

    /**
     * {@inheritDoc}
     * @throws \Exception
     */
    public function get(string $collection_name, string $key, mixed $by, string $operator): mixed
    {
        $collection_data = $this->all($collection_name);
        if(empty($collection_data)) {
            return [];
        }
        $casting_to = $this->validateKey($collection_name, $key);
        return array_values( array_filter($collection_data,function ($unfiltered) use ($key, $by, $operator, $casting_to){
            if(isset($unfiltered[$key])) {

                if($operator === '=' && $unfiltered[$key] === $this->castingTo($casting_to, $by)) {
                    return $unfiltered;
                }
                elseif ($operator === '!=' && $unfiltered[$key] !== $this->castingTo($casting_to, $by)) {
                    return $unfiltered;
                }
                elseif ($operator === '<=' && $unfiltered[$key] <= $this->castingTo($casting_to, $by)) {
                    return $unfiltered;
                }
                elseif ($operator === '>=' && $unfiltered[$key] >= $this->castingTo($casting_to, $by)) {
                    return $unfiltered;
                }
            }
        }));
    }

    /**
     * {@inheritDoc}
     * @throws \Exception
     */
    public function inRange(string $collection_name, string $key, int $min, int $max): mixed
    {
        $collection_data = $this->all($collection_name);
        $casting_to = $this->validateKey($collection_name, $key);
        return array_values(array_filter($collection_data,function ($unfiltered) use ($key,$casting_to,$min,$max){
            if(isset($unfiltered[$key])) {
                if($unfiltered[$key] >= $this->castingTo($casting_to, $min) && $unfiltered[$key] <= $this->castingTo($casting_to, $max))
                {
                    return $unfiltered;
                }
            }
        }));
    }

    /**
     * {@inheritDoc}
     * @throws \Exception
     */
    public function inWithin(string $collection_name, string $key, array $by): mixed
    {
        $collection_data = $this->all($collection_name);
        $casting_to = $this->validateKey($collection_name, $key);
        foreach ($by as $k=>$value) {
           $by[$k] = $this->castingTo($casting_to, $value);
        }
        return array_values(array_filter($collection_data,function ($unfiltered) use ($key,$by){
            if(isset($unfiltered[$key])) {
                if(in_array($unfiltered[$key],$by))
                {
                    return $unfiltered;
                }
            }
        }));
    }

    /**
     * {@inheritDoc}
     * @throws \Exception
     */
    public function notWithin(string $collection_name, string $key, array $by): mixed
    {
        $collection_data = $this->all($collection_name);
        $casting_to = $this->validateKey($collection_name, $key);
        foreach ($by as $k=>$value) {
            $by[$k] = $this->castingTo($casting_to, $value);
        }
        return array_values(array_filter($collection_data,function ($unfiltered) use ($key,$by){
            if(isset($unfiltered[$key])) {
                if(!in_array($unfiltered[$key], $by))
                {
                    return $unfiltered;
                }
            }
        }));
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

    public function includes(string $collection_name, string $key, string $by): mixed
    {
        $collection_data = $this->all($collection_name);
        $casting_to = $this->validateKey($collection_name, $key);
        return array_values( array_filter($collection_data,function ($unfiltered) use ($key, $by, $casting_to){
            if(isset($unfiltered[$key]) && str_contains($unfiltered[$key],$this->castingTo($casting_to, $by))) {
                return $unfiltered;
            }
        }));
    }
}