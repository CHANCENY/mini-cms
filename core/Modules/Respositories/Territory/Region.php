<?php

namespace Mini\Cms\Modules\Respositories\Territory;

class Region extends Regions
{
    private array $region;

    public function __construct(int $region_id)
    {
        parent::__construct();
        $this->region = $this->getRegion($region_id);
    }

    // Method to get the ID
    public function getId()
    {
        return $this->region['id'];
    }

    // Method to get the name
    public function getName()
    {
        return $this->region['name'];
    }

    // Method to get the translations
    public function getTranslations()
    {
        return $this->region['translations'];
    }

    // Method to get the WikiData ID
    public function getWikiDataId()
    {
        return $this->region['wikiDataId'];
    }
}
