<?php

namespace Mini\Cms\Modules\Respositories\Territory;

class Countries
{
    private mixed $countries;

    public function getCountries(): mixed
    {
        return $this->countries;
    }

    /**
     * Load country.
     */
    public function __construct()
    {
        $path = __DIR__.'/data/countries.json';
        if (file_exists($path)) {
            $this->countries = json_decode(file_get_contents($path), true);
        }
    }

    /**
     * Get Country
     * @param string $code
     * @return array
     */
    public function getCountry(string $code): array {
        $country = array_filter($this->countries, function($country) use ($code) {
            return $country['iso2'] == $code || $country['iso3'] == $code;
        });
        return reset($country);
    }
}