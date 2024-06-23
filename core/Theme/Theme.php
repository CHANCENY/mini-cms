<?php

namespace Mini\Cms\Theme;

use DOMDocument;
use Mini\Cms\Configurations\ConfigFactory;
use Mini\Cms\Controller\Request;
use Mini\Cms\Controller\Route;
use Mini\Cms\Modules\CurrentUser\CurrentUser;
use Mini\Cms\Modules\Extensions\Extensions;
use Mini\Cms\Modules\MetaTag\MetaTag;
use Mini\Cms\Modules\Respositories\Territory\AddressFormat;
use Mini\Cms\Modules\Site\Site;
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
        $this->assets = [];

        // Finding assets.
        if(!Theme::isDefaultTheme('default_admin')) {
            $theme_source = Theme::themeSource('default_admin');
           $this->assets = json_decode(file_get_contents(trim($theme_source,'/') . "/__theme_libraries.json"),true);
        }
        $custom = json_decode(file_get_contents(trim($this->theme_source,'/') . "/__theme_libraries.json"),true);
        if(!empty($custom['head'])) {
            foreach ($custom['head'] as $head) {
                $this->assets['head'][] = $head;
            }
        }
        if(!empty($custom['footer'])) {
            foreach ($custom['footer'] as $head) {
                $this->assets['footer'][] = $head;
            }
        }
        if(!empty($custom['global'])) {
            foreach ($custom['global'] as $head) {
                $this->assets['global'][] = $head;
            }
        }
        Extensions::runHooks('_attachments_assets',[&$this->assets]);
    }

    public function view(string $file_name, $options = []): ?string
    {
        $finder = new FileLoader($this->theme_source);
        $view_file = $finder->findFiles($file_name);
        if(count($view_file) > 1) {
            throw new \Exception('Multiple view file detected ('.$file_name.')');
        }

        $currentUser = new CurrentUser();
        $route = Tempstore::load('current_route');
        if(empty($view_file[0]) && $file_name !== 'navigation.php') {
            if($route instanceof Route) {
                $loaded = $route->getLoadedRoute();
                if($loaded instanceof \Mini\Cms\Routing\Route){
                    if($loaded->isUserAllowed($currentUser->getRoles())){
                        $default_theme = Theme::override('default_admin');
                        if($default_theme instanceof Theme) {
                            $theme_source = $default_theme->getThemeSource();
                            $finder = new FileLoader($theme_source);
                            $view_file = $finder->findFiles($file_name);
                        }
                    }
                }
            }
        }

        $variables = [
            'content'=>$options,
            'current_route' => $route,
            'current_user' => new CurrentUser(),
            'current_request' => Request::createFromGlobals(),
            'site' => new Site(),
        ];
        if(!empty($view_file[0]) && file_exists($view_file[0])) {
            ob_start();
            extract($variables);
            require $view_file[0];
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
        $theme = Tempstore::load('theme_loaded');
        $default_navigation = null;
        if($theme instanceof Theme) {
            $navigation = Tempstore::load('theme_navigation');
            //TODO: calling menus_alter hook.
            if($navigation instanceof Menus) {
                $currentUser = new CurrentUser();
                if($currentUser->isAdmin()) {
                    $default_theme = Theme::override('default_admin');
                    if(!Theme::isDefaultTheme('default_admin')) {
                       $default_navigation = $default_theme->view('navigation.php',$navigation);
                    }
                }
                return $default_navigation . Tempstore::load('theme_loaded')->view('navigation.php',$navigation);
            }
        }
        return null;
    }

    public function writeFooter(): string|null
    {
        $footer = Tempstore::load('theme_footer');
        if($footer instanceof FooterInterface) {
            // TODO call hook footer_alter
            $renderArray = $footer->render();
            return $this->view($renderArray['theme'] ?? '', $renderArray['options'] ?? []);
        }
        return "";
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
        $metadata = Tempstore::load('theme_meta_tags');
        if($metadata instanceof MetaTag) {
            //TODO call meta_tag_alter.
            return $metadata->__toString();
        }
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
            $themes = $config->get('theme');
            $theme_default = array_filter($themes,function ($item) use ($theme){
                return $item['name'] === $theme;
            });
            if($theme_default) {
                return new Theme(reset($theme_default));
            }
        }
        return null;
    }

    public static function isDefaultTheme(string $theme): bool
    {
        $config = Services::create('config.factory');
        if($config instanceof ConfigFactory) {
            $themes = $config->get('theme');
            $theme_default = array_filter($themes,function ($item) use ($theme){
                return $item['name'] === $theme && $item['active'] === true;
            });
            if($theme_default) {
               return true;
            }
        }
        return false;
    }

    public static function themeSource(string $theme): ?string
    {
        $config = Services::create('config.factory');
        if($config instanceof ConfigFactory) {
            $themes = $config->get('theme');
            $theme_default = array_filter($themes,function ($item) use ($theme){
                return $item['name'] === $theme;
            });
            if($theme_default) {
                return reset($theme_default)['source_directory'];
            }
        }
        return null;
    }

    public function processBuildContentHtml(string $content): string
    {
        // Disable DOMDocument warnings
        libxml_use_internal_errors(true);

        // Create a DOMDocument object
        $dom = new DOMDocument();

        // Load the HTML content into the DOMDocument object
        $dom->loadHTML($content);

        // Get all anchor tags
        $anchors = $dom->getElementsByTagName('a');

        // Iterate over each anchor tag
        foreach ($anchors as $anchor) {
            // Check if the aria-label attribute is set
            if (!$anchor->hasAttribute('aria-label')) {
                // If not set, use the text content of the anchor tag as the aria-label
                $linkText = $anchor->nodeValue;
                $anchor->setAttribute('aria-label', trim($linkText));
            }

            // Check if the title attribute is set
            if (!$anchor->hasAttribute('title')) {
                // If not set, use the text content of the anchor tag as the title attribute
                $linkText = $anchor->nodeValue;
                $anchor->setAttribute('title', trim($linkText));
            }
        }

        $images = $dom->getElementsByTagName('img');

        // Get all img tags
        $images = $dom->getElementsByTagName('img');

        // Iterate over each img tag
        foreach ($images as $image) {

            // Get the source (src) attribute of the image
            $src = $image->getAttribute('src');

            // Extract the file name from the source URL
            $fileName = basename($src);

            // Remove file extension from the file name
            $fileName = pathinfo($fileName, PATHINFO_FILENAME);

            // Set the alt attribute to the file name
            $image->setAttribute('alt', $fileName);
        }

        $inputs = $dom->getElementsByTagName('input');
        $file_input_exist = false;
        $address_field_exists = false;

        foreach ($inputs as $index => $input) {
            // Get input type and name
            $type = strtolower($input->getAttribute('type'));
            $name = $input->getAttribute('name');

            if($type === 'file') {
                $file_input_exist = true;
            }

            // Generate unique ID for input element
            $inputId = $input->hasAttribute('id') ? $input->getAttribute('id') : 'input-' . $index;
            if($input->getAttribute('class') === 'field-field-address-field') {
                $address_field_exists = true;
            }

            // Create label text
            $labelText = 'Field ' . ($name !== '' ? $name : 'input');

            // Create hidden label element
            $label = $dom->createElement('label');
            $label->setAttribute('for', $inputId);
            $label->setAttribute('style', 'display: none;');
            $label->nodeValue = $labelText;

            // Insert label element before input element
            $input->parentNode->insertBefore($label, $input);

            // Set aria-labelledby attribute for input
            $input->setAttribute('aria-labelledby', trim($inputId));
        }
        // Return the modified HTML content

        $file_script = null;
        if($file_input_exist) {
            $file_script = "<script type='text/javascript'>".file_get_contents('../core/default/themes/mini_cms/assets/js/file_manager.js') . "</script>";
        }
        if($address_field_exists) {
            $file_script .= "<script type='text/javascript'>".file_get_contents(AddressFormat::addressAsset()) . "</script>";
        }

        $dom_content = $dom->saveHTML();

        // Add default assets.
        $dom_content = str_replace('{{DEFAULTS_ASSETS}}', $file_script, $dom_content);
        return $dom_content;
    }

}