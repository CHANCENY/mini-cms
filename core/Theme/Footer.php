<?php

namespace Mini\Cms\Theme;

use Mini\Cms\Configurations\ConfigFactory;
use Mini\Cms\Modules\Storage\Tempstore;

class Footer implements FooterInterface
{

    /**
     * @var string
     */
    private string $footerFile;

    /**
     * {@inheritdoc }
     */
    public function render(): array
    {
         // TODO add more info.
           $options = [
               'current_route' => Tempstore::load('current_route'),
               'current_user' => [],
               'site_config' => (new ConfigFactory())->get('site_information')
           ];
        return [
            'theme' => $this->footerFile,
            'options' => $options,
        ];
    }

    /**
     * {@inheritdoc }
     */
    public function themeFile(string $file): void
    {
        $this->footerFile = $file;
    }
}