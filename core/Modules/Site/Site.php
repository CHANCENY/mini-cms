<?php

namespace Mini\Cms\Modules\Site;

use Mini\Cms\Configurations\ConfigFactory;
use Mini\Cms\Services\Services;

class Site
{
    private array $siteInformation = [
        'BrandingAssets' => [
            'Logo' => '',
            'Phone' => '',
            'Name' => '',
            'Email' => '',
        ],
        'ContactInformation' => [
            'Smtp' => [
                'smtp_server' => '',
                'smtp_port' => '',
                'smtp_username' => '',
                'smtp_password' => ''
            ],
        ],
    ];

    public function __construct()
    {
        $config = Services::create('config.factory');
        if($config instanceof ConfigFactory && !empty($config->get('site_information'))) {
            $this->siteInformation = $config->get('site_information');
        }
    }

    // Setter method for setting ContactInformation
    public function setContactInformation(string $type, mixed $value): void
    {
        $type = ucfirst($type); // Capitalize the type name
        if (array_key_exists($type, $this->siteInformation['ContactInformation'])) {
            $this->siteInformation['ContactInformation'][$type] = $value;
        }
    }

    // Setter method for setting BrandingAssets
    public function setBrandingAssets(string $asset, mixed $value): void
    {
        $asset = ucfirst($asset); // Capitalize the asset name
        if (array_key_exists($asset, $this->siteInformation['BrandingAssets'])) {
            $this->siteInformation['BrandingAssets'][$asset] = $value;
        }
    }

    // Getter method for ContactInformation
    public function getContactInformation(string $type): mixed
    {
        $type = ucfirst($type); // Capitalize the type name
        return $this->siteInformation['ContactInformation'][$type] ?? null;
    }

    // Getter method for BrandingAssets
    public function getBrandingAssets(string $asset): mixed
    {
        $asset = ucfirst($asset); // Capitalize the asset name
        return $this->siteInformation['BrandingAssets'][$asset] ?? null;
    }

    public function save(): bool
    {
        $config = Services::create('config.factory');
        if($config instanceof ConfigFactory) {
            $config->set('site_information', $this->siteInformation);
            return $config->save();
        }
        return false;
    }
}
