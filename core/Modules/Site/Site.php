<?php

namespace Mini\Cms\Modules\Site;

use Mini\Cms\Configurations\ConfigFactory;
use Mini\Cms\Services\Services;

class Site
{
    private array $siteInformation = [
        'DomainName' => '',
        'Purpose' => '',
        'TargetAudience' => [
            'Demographics' => '',
            'Interests' => ''
        ],
        'BrandingAssets' => [
            'Logo' => '',
            'Slogan' => '',
            'Name' => ''
        ],
        'LegalInformation' => [
            'PrivacyPolicy' => '',
            'TermsOfService' => ''
        ],
        'ContactInformation' => [
            'Email' => '',
            'Smtp' => [
                'stmp_server' => '',
                'stmp_port' => '',
                'stmp_username' => '',
                'stmp_password' => ''
            ],
        ],
        'SocialMediaIntegration' => [
            'Facebook' => '',
            'Instagram' => '',
            'Twitter' => '',
            'LinkedIn' => '',
            'WhatsApp' => ''
        ],
    ];

    public function __construct()
    {
        $config = Services::create('config.factory');
        if($config instanceof ConfigFactory && !empty($config->get('site_information'))) {
            $this->siteInformation = $config->get('site_information');
        }
    }

    // Setter method for setting LegalInformation
    public function setLegalInformation(string $type, mixed $content): void
    {
        $type = ucfirst($type); // Capitalize the type name
        if (array_key_exists($type, $this->siteInformation['LegalInformation'])) {
            $this->siteInformation['LegalInformation'][$type] = $content;
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

    // Setter method for setting TargetAudience
    public function setTargetAudience(string $category, string $value): void
    {
        $category = ucfirst($category); // Capitalize the category name
        if (array_key_exists($category, $this->siteInformation['TargetAudience'])) {
            $this->siteInformation['TargetAudience'][$category] = $value;
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

    // Getter method for LegalInformation
    public function getLegalInformation(string $type): ?string
    {
        $type = ucfirst($type); // Capitalize the type name
        return $this->siteInformation['LegalInformation'][$type] ?? null;
    }

    // Getter method for ContactInformation
    public function getContactInformation(string $type): mixed
    {
        $type = ucfirst($type); // Capitalize the type name
        return $this->siteInformation['ContactInformation'][$type] ?? null;
    }

    // Getter method for TargetAudience
    public function getTargetAudience(string $category): ?string
    {
        $category = ucfirst($category); // Capitalize the category name
        return $this->siteInformation['TargetAudience'][$category] ?? null;
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

    public function setDomain(float|bool|int|string|null $get): void
    {
        $this->siteInformation['DomainName'] = $get;
    }

    public function setPurpose(float|bool|int|string|null $get): void
    {
        $this->siteInformation['Purpose'] = $get;
    }

    public function setSocial(array $array): void
    {
        $this->siteInformation['SocialMediaIntegration'] = array_merge($this->siteInformation['SocialMediaIntegration'], $array);
    }
}
