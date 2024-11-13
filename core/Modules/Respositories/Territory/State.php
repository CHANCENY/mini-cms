<?php

namespace Mini\Cms\Modules\Respositories\Territory;

/**
 *
 */
class State extends States
{
    /**
     * @var array|mixed
     */
    private array $state;

    /**
     * @param string $country_code
     * @param string $state_code
     */
    public function __construct(string $country_code, string $state_code)
    {
        parent::__construct();
        $this->state = $this->getState($country_code, $state_code);
    }

    /**
     * @return mixed
     */
    public function getId(): mixed
    {
        return $this->state['id'];
    }

    /**
     * @return mixed
     */
    public function getName(): mixed
    {
        return $this->state['name'];
    }

    /**
     * @return mixed
     */
    public function getCountryId(): mixed
    {
        return $this->state['country_id'];
    }

    /**
     * @return mixed
     */
    public function getCountryCode(): mixed
    {
        return $this->state['country_code'];
    }

    /**
     * @return mixed
     */
    public function getCountryName(): mixed
    {
        return $this->state['country_name'];
    }

    /**
     * @return mixed
     */
    public function getStateCode(): mixed
    {
        return $this->state['state_code'];
    }

    /**
     * @return mixed
     */
    public function getType(): mixed
    {
        return $this->state['type'];
    }

    /**
     * @return mixed
     */
    public function getLatitude(): mixed
    {
        return $this->state['latitude'];
    }

    /**
     * @return mixed
     */
    public function getLongitude(): mixed
    {
        return $this->state['longitude'];
    }
}
