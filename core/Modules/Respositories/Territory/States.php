<?php

namespace Mini\Cms\Modules\Respositories\Territory;

class States
{
    private mixed $states;

    public function __construct()
    {
        $path = __DIR__ . '/data/states.json';
        if (file_exists($path)) {
            $this->states = json_decode(file_get_contents($path), true);
        }
    }

    public function getStates(string $country_code): array {
        $states = array_filter($this->states, function($state) use ($country_code) {
            return $state['country_code'] === $country_code;
        });
        return reset($states);
    }

    public function getState(string $country_code, string $state_code): mixed {
        $state = array_filter($this->states, function($state) use ($country_code, $state_code) {
            return $state['country_code'] === $country_code && $state['state_code'] === $state_code;
        });
        return reset($state);
    }

}