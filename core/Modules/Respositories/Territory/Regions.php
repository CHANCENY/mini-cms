<?php

namespace Mini\Cms\Modules\Respositories\Territory;

class Regions
{
    private mixed $regions;

    public function __construct()
    {
        $path = __DIR__ . '/data/regions.json';
        if (file_exists($path)) {
            $this->regions = json_decode(file_get_contents($path), true);
        }
    }

    public function getRegion(int $region_id): array {
        $region = array_filter($this->regions, function ($region) use ($region_id) {
            return $region['id'] === $region_id;
        });
        return reset($region);
    }

    public function getRegions(): array
    {
        return $this->regions;
    }
}