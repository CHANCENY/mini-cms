<?php

namespace Mini\Cms\Theme;

use Mini\Cms\Configurations\ConfigFactory;
use Mini\Cms\Modules\Storage\Tempstore;
use Mini\Cms\Services\Services;

class Theme
{
    private ?string $them_title;
    private ?string $theme_description;
    private ?string $theme_source;
    private mixed $assets;

    public function getThemTitle(): ?string
    {
        return $this->them_title;
    }

    public function getThemeDescription(): ?string
    {
        return $this->theme_description;
    }

    public function getThemeSource(): ?string
    {
        return $this->theme_source;
    }

    public function __construct(array $theme)
    {
        $this->them_title = $theme['title'] ?? null;
        $this->theme_description = $theme['description'] ?? null;
        $this->theme_source = $theme['source_directory'] ?? null;

        // Finding assets.
        $this->assets = json_decode(file_get_contents(trim($this->theme_source,'/') . "/__theme_libraries.json"),true);
    }

    public function view(string $file_name, $options = []): ?string
    {
        $finder = new FileLoader($this->theme_source);
        $view_file = $finder->findFiles($file_name);
        if(count($view_file) > 1) {
            throw new \Exception('Multiple view file detected ('.$file_name.')');
        }

        $variables = [
            'content'=>$options,
            'current_route' => Tempstore::load('current_route'),
            'current_user' => [],
        ];
        if(file_exists($view_file[0])) {
            ob_start();
            extract($variables);
            require_once $view_file[0];
            return ob_get_clean();
        }
        return null;
    }

    public static function loader(): ?Theme
    {
        $config = Services::create('config.factory');
        if($config instanceof ConfigFactory) {
            $theme = $config->get('theme');
            $theme_default = array_filter($theme,function ($item){
                 return $item['active'] === true;
            });
            if($theme_default) {
                return new Theme(reset($theme_default));
            }
        }
        return null;
    }

    public function writeNavigation(): string|null
    {
        $navigation = Tempstore::load('theme_navigation');
        if($navigation instanceof Menus) {
            return Tempstore::load('theme_loaded')->view('navigation.php',$navigation);
        }
        return null;
    }

    public function writeFooter(): string
    {
        return "<p>@copyrights reserved</p>";
    }

    public function writeAssets(string $assets_section): ?string
    {
        return $this->buildAssets($this->assets[$assets_section] ?? []);
    }

    private function buildAssets(mixed $assets_section): ?string
    {
        $assets_line = null;
        foreach ($assets_section as $value) {

            // Build up options.
            $list = explode('|', $value);
            $option = end($list);
            $link = $list[0];

            $extension = explode('.', $link);
            $extension = end($extension);

            if(strtolower($extension) === 'js') {
                // Link set-up.
                $assets_line .= match ($option) {
                    'defer', 'async' => "<script type='text/javascript' src='$link' $option></script>".PHP_EOL,
                    'module' => "<script type='$option' src='$link'></script>". PHP_EOL,
                    default => "<script type='text/javascript' src='$link'></script>". PHP_EOL,
                };
            }
            elseif (strtolower($extension) === 'css') {
                // Link set-up.
                $assets_line .= "<link rel='stylesheet' type='text/css' href='$link'>".PHP_EOL;
            }
            elseif (strtolower($option) === 'css-font'){
                $assets_line .= "<link rel='stylesheet' type='text/css' href='$link'>".PHP_EOL;
            }
            elseif (strtolower($option) === 'js-lib'){
                $assets_line .= "<script type='text/javascript' src='$link'></script>". PHP_EOL;
            }
            elseif (strtolower($option) === 'ext-lib') {
                $assets_line .= $link  . PHP_EOL;
            }
        }
        return $assets_line;
    }

    public function writeMetaTag(): string
    {
        return "";
    }

    public function writeHtmlAttribute(): string
    {
        return "";
    }

    public static function override(string $theme): ?Theme
    {
        $config = Services::create('config.factory');
        if($config instanceof ConfigFactory) {
            $theme = $config->get('theme');
            $theme_default = array_filter($theme,function ($item){
                return $item['active'] === true;
            });
            if($theme_default) {
                return new Theme(reset($theme_default));
            }
        }
        return null;
    }
}