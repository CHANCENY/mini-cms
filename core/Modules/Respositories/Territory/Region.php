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
}