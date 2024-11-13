<?php

namespace Mini\Cms\Modules\Respositories\Territory;

/**
 * Country handle.
 */
class Country extends Countries
{
    /**
     * @var array
     */
    private array $country;

    /**
     * @param string $code
     */
    public function __construct(string $code)
    {
        parent::__construct();
        $this->country = $this->getCountry($code);
    }

    /**
     * @return mixed
     */
    public function getId(): mixed
    {
        return $this->country['id'];
    }

    /**
     * @return mixed
     */
    public function getName(): mixed
    {
        return $this->country['name'];
    }

    /**
     * @return mixed
     */
    public function getIso3(): mixed
    {
        return $this->country['iso3'];
    }

    /**
     * @return mixed
     */
    public function getIso2(): mixed
    {
        return $this->country['iso2'];
    }

    /**
     * @return mixed
     */
    public function getNumericCode(): mixed
    {
        return $this->country['numeric_code'];
    }

    /**
     * @return mixed
     */
    public function getPhoneCode(): mixed
    {
        return $this->country['phone_code'];
    }

    /**
     * @return mixed
     */
    public function getCapital(): mixed
    {
        return $this->country['capital'];
    }

    /**
     * @return mixed
     */
    public function getCurrency(): mixed
    {
        return $this->country['currency'];
    }

    /**
     * @return mixed
     */
    public function getCurrencyName(): mixed
    {
        return $this->country['currency_name'];
    }

    /**
     * @return mixed
     */
    public function getCurrencySymbol(): mixed
    {
        return $this->country['currency_symbol'];
    }

    /**
     * @return mixed
     */
    public function getTld(): mixed
    {
        return $this->country['tld'];
    }

    /**
     * @return mixed
     */
    public function getNative(): mixed
    {
        return $this->country['native'];
    }

    /**
     * @return mixed
     */
    public function getRegion(): mixed
    {
        return $this->country['region'];
    }

    /**
     * @return mixed
     */
    public function getRegionId(): mixed
    {
        return $this->country['region_id'];
    }

    /**
     * @return mixed
     */
    public function getSubregion(): mixed
    {
        return $this->country['subregion'];
    }

    /**
     * @return mixed
     */
    public function getSubregionId(): mixed
    {
        return $this->country['subregion_id'];
    }

    /**
     * @return mixed
     */
    public function getNationality(): mixed
    {
        return $this->country['nationality'];
    }

    /**
     * @return mixed
     */
    public function getTimezones(): mixed
    {
        return $this->country['timezones'];
    }

    /**
     * @return mixed
     */
    public function getTranslations(): mixed
    {
        return $this->country['translations'];
    }

    /**
     * @return mixed
     */
    public function getLatitude(): mixed
    {
        return $this->country['latitude'];
    }

    /**
     * @return mixed
     */
    public function getLongitude(): mixed
    {
        return $this->country['longitude'];
    }

    /**
     * @return mixed
     */
    public function getEmoji(): mixed
    {
        return $this->country['emoji'];
    }

    /**
     * @return mixed
     */
    public function getEmojiU(): mixed
    {
        return $this->country['emojiU'];
    }
}
