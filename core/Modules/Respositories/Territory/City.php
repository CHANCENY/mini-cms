<?php

namespace Mini\Cms\Modules\Respositories\Territory;



class City extends Cities
{
    private mixed $city;

    public function __construct(string $country_code, int $city_id)
    {
        parent::__construct();
        $this->city = $this->getCityByCountryCode($country_code,$city_id);
    }

    // Method to get the ID
    public function getId()
    {
        return $this->city['id'];
    }

    // Method to get the name
    public function getName()
    {
        return $this->city['name'];
    }

    // Method to get the state ID
    public function getStateId()
    {
        return $this->city['state_id'];
    }

    // Method to get the state code
    public function getStateCode()
    {
        return $this->city['state_code'];
    }

    // Method to get the state name
    public function getStateName()
    {
        return $this->city['state_name'];
    }

    // Method to get the country ID
    public function getCountryId()
    {
        return $this->city['country_id'];
    }

    // Method to get the country code
    public function getCountryCode()
    {
        return $this->city['country_code'];
    }

    // Method to get the country name
    public function getCountryName()
    {
        return $this->city['country_name'];
    }

    // Method to get the latitude
    public function getLatitude()
    {
        return $this->city['latitude'];
    }

    // Method to get the longitude
    public function getLongitude()
    {
        return $this->city['longitude'];
    }

    // Method to get the WikiData ID
    public function getWikiDataId()
    {
        return $this->city['wikiDataId'];
    }
}
