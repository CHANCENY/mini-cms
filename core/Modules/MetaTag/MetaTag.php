<?php

namespace Mini\Cms\Modules\MetaTag;

class MetaTag
{
    private array $metaTags;

    public function __construct()
    {
        //TODO: bring in site config
        $config_site = [];
        $this->metaTags = [
            MetagEnum::Title->name => [
                'value' => $config_site['site_title'] ?? 'Mini CMS',
                'tag' => MetagEnum::Title
            ],
            MetagEnum::Description->name => [
                'value' => $config_site['site_description'] ?? 'This is mini cms site description',
                'tag' => MetagEnum::Description
            ],
            MetagEnum::Author->name => [
                'value' => $config_site['site_author'] ?? 'Chance',
                'tag' => MetagEnum::Author
            ],
            MetagEnum::Icon->name => [
                'value' => $config_site['site_icon'] ?? 'http://localhost/favicon.ico',
                'type' => 'ico',
                'tag' => MetagEnum::Icon
            ],
            MetagEnum::Canonical->name => [
                'value' => $config_site['site_canonical'] ?? 'http://localhost/',
                'tag' => MetagEnum::Canonical
            ]
        ];
    }

    public function set(MetagEnum $meta, $value): void
    {
        $this->metaTags[$meta->name] = $value;
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