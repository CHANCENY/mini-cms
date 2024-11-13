<?php

namespace Mini\Cms\Modules\Respositories\Territory;

class Cities
{
    private mixed $cities;

    public function __construct()
    {
        $this->cities = [];
        $path = __DIR__ . '/data/cities.json';
        if (file_exists($path)) {
            $this->cities = json_decode(file_get_contents($path), true);
        }
    }

    public function getCities(): mixed
    {
        return $this->cities;
    }

    public function getCitiesStateCode(string $state_code, string $country_code): mixed
    {
        return array_filter($this->cities, function ($city) use ($state_code, $country_code) {
            return $city['state_code'] === $state_code && $city['country_code'] === $country_code;
        });
    }

    public function getCitiesByCountryCode(string $country_code): mixed
    {
        return array_filter($this->cities, function ($city) use ($country_code) {
            return $city['country_code'] === $country_code;
        });
    }

    public function getCityByCountryCode(string $country_code, int $city_id): mixed
    {
        $city = array_filter($this->cities, function ($city) use ($country_code, $city_id) {
            return $city['country_code'] === $country_code && $city['id'] === $city_id;
        });
        return reset($city);
    }
}