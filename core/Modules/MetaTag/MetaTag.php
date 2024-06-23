<?php

namespace Mini\Cms\Modules\MetaTag;

use Mini\Cms\Controller\Route;
use Mini\Cms\Modules\FileSystem\File;
use Mini\Cms\Modules\Site\Site;
use Mini\Cms\Modules\Storage\Tempstore;
use Mini\Cms\Services\Services;

class MetaTag
{
    private array $metaTags;

    public function __construct()
    {
        //TODO: bring in site config
        $config_site = Services::create('config.site');
        if($config_site instanceof Site) {
            $file = File::load($config_site->getBrandingAssets('Logo')['fid'] ?? 0);
            $logo = null;
            if($file instanceof File) {
                $logo = "/". $file->getFilePath();
            }
            $route = Tempstore::load('current_route');
            $url = null;
            if($route instanceof Route) {
                $url = $route->getCurrentUri();

            }
            $this->metaTags = [
                MetagEnum::Title->name => [
                    'value' => $config_site->getBrandingAssets('name'),
                    'tag' => MetagEnum::Title
                ],
                MetagEnum::Description->name => [
                    'value' => $config_site->getBrandingAssets('Slogan'),
                    'tag' => MetagEnum::Description
                ],
                MetagEnum::Author->name => [
                    'value' => $config_site->getBrandingAssets('Name'),
                    'tag' => MetagEnum::Author
                ],
                MetagEnum::Icon->name => [
                    'value' => $logo,
                    'type' => 'ico',
                    'tag' => MetagEnum::Icon
                ],
                MetagEnum::Canonical->name => [
                    'value' => $url,
                    'tag' => MetagEnum::Canonical
                ],
                MetagEnum::Charset->name =>[
                    'value' => 'UTF-8',
                    'tag' => MetagEnum::Charset
                ]
            ];
        }
    }

    public function set(MetagEnum $meta, $value): void
    {
        $this->metaTags[$meta->name] = [
            'value' => $value,
            'tag' => $meta
        ];
    }

    public function remove(MetagEnum $meta): bool
    {
        if(isset($this->metaTags[$meta->name])) {
            unset($this->metaTags[$meta->name]);
            return true;
        }
        return false;
    }

    public function __toString(): string
    {
        $meta_tag_line = null;
        foreach($this->metaTags as $name => $value) {
            if($name === MetagEnum::Icon->name) {
                $meta_tag_line .= str_replace(['{{TYPE}}', '{{VALUE}}'], [$value['type'], $value['value']], $value['tag']->value).PHP_EOL;
            }
            else {
                $meta_tag_line .= str_replace('{{VALUE}}', $value['value'], $value['tag']->value). PHP_EOL;
            }
        }
        return $meta_tag_line;
    }
}