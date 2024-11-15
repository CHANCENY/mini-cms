<?php

namespace Mini\Cms\Theme;

use DOMDocument;
use Mini\Cms\Configurations\ConfigFactory;
use Mini\Cms\Controller\Request;
use Mini\Cms\Controller\Route;
use Mini\Cms\Mini;
use Mini\Cms\Modules\Cache\Caching;
use Mini\Cms\Modules\CurrentUser\CurrentUser;
use Mini\Cms\Modules\Extensions\Extensions;
use Mini\Cms\Modules\MetaTag\MetaTag;
use Mini\Cms\Modules\Respositories\Territory\AddressFormat;
use Mini\Cms\Modules\Site\Site;
use Mini\Cms\Modules\Storage\Tempstore;
use Mini\Cms\Modules\Themes\ThemeExtension;
use Mini\Cms\Services\Services;
use Symfony\Component\Yaml\Yaml;

class Theme
{
    private ?string $them_title;
    private ?string $theme_description;
    private ?string $theme_source;
    private mixed $assets;

    private string $version;

    public function getVersion(): string
    {
        return $this->version;
    }

    private ?string $theme_name;

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
        $this->theme_name = $theme['name'] ?? null;
        $this->version = $theme['version'] ?? null;

        // Finding assets.
        if(!Theme::isDefaultTheme('default_admin')) {
            $theme_source = Theme::themeSource('default_admin');
           $this->assets = Yaml::parseFile(trim($theme_source,'/') . "/__theme_libraries.yml");
        }
        $custom = Yaml::parseFile(trim($this->theme_source,'/') . "/__theme_libraries.yml");
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
        $theme_tag = $this->theme_name.'-'.$this->version;
        $has_build = Caching::cache()->is_exists($theme_tag);
        $view_file = [];
        if($has_build) {
            $files = Caching::cache()->get($theme_tag);
            $count = count($files);
            for ($i = 0; $i < $count; $i++) {
                $filename = explode('/', $files[$i]);
                if(end($filename) === $file_name) {
                    $view_file[] = $files[$i];
                }
            }
        }
        else {
            $finder = new FileLoader($this->theme_source);
            $view_file = $finder->findFiles($file_name);
        }

        if(count($view_file) > 1) {
            throw new \Exception('Multiple view file detected ('.$file_name.')');
        }
        $currentUser = new CurrentUser();
        $route = get_global('current_route');
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

        if(empty($view_file)) {
            $active_theme = ThemeExtension::getActiveTheme();
            $default_theme = ThemeExtension::getDefaultTheme('default_admin');
            if($active_theme['name'] !== $default_theme['name']) {
                $active_theme = Theme::override($active_theme['name']);
                if($active_theme instanceof Theme) {
                    $theme_source = $active_theme->getThemeSource();
                    $finder = new FileLoader($theme_source);
                    $view_file = $finder->findFiles($file_name);
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
        Extensions::runHooks('_view_data_alter', [&$variables, $file_name]);
        if(!empty($view_file[0]) && file_exists($view_file[0])) {
            ob_start();
            extract($variables);
            require get_global('mini_wrapper_class')->getRealPath($view_file[0]);
            return ob_get_clean();
        }
        return null;
    }


    public static function loader(): ?Theme
    {
        $themes = ThemeExtension::getThemes();
        Extensions::runHooks('_themes_list_alter',[&$themes]);
        $theme_default = array_filter($themes,function ($item){
            return $item['active'] === 1 || $item['active'] === true;
        });
        if($theme_default) {
            return new Theme(reset($theme_default));
        }
        else {
            return new Theme(ThemeExtension::getDefaultTheme('default_admin'));
        }
    }

    public function writeNavigation(): string|null
    {
        $theme = get_global('theme_loaded');
        $default_navigation = null;
        if($theme instanceof Theme) {
            $navigation = get_global('theme_navigation');
            //TODO: calling menus_alter hook.
            if($navigation instanceof Menus) {
                $currentUser = new CurrentUser();
                if($currentUser->isAdmin()) {
                    $default_theme = Theme::override('default_admin');
                    if($theme->getThemeName() !== $default_theme->getThemeName()) {
                       $default_navigation = $default_theme->view('navigation.php',$navigation);
                    }
                }
                $navigation_file = 'navigation.php';
                Extensions::runHooks('_navigation_template_alter', [&$navigation_file]);
                return $default_navigation . get_global('theme_loaded')->view($navigation_file,$navigation);
            }
        }
        return null;
    }

    public function writeFooter(): string|null
    {
        $footer = get_global('theme_footer');
        if($footer instanceof FooterInterface) {
            // TODO call hook footer_alter
            $renderArray = $footer->render();
            Extensions::runHooks('_footer_template_alter',[&$renderArray]);
            return $this->view($renderArray['theme'] ?? '', $renderArray['options'] ?? []);
        }
        return "";
    }

    public function writeAssets(string $assets_section): ?string
    {
        $current_route = get_global('current_route');
        Extensions::runHooks('_pre_assets_build',[&$this->assets[$assets_section], $assets_section, $current_route]);
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
        $metadata = get_global('theme_meta_tags');
        if($metadata instanceof MetaTag) {
            Extensions::runHooks('_meta_pre_render_alter', [&$metadata]);
            return $metadata->__toString();
        }
        return "";
    }

    public function writeHtmlAttribute(): string
    {
        $html_attribute = [];
        Extensions::runHooks('_html_attribute_alter',[&$html_attribute]);
        return implode(' ', $html_attribute);
    }

    public static function override(string $theme): ?Theme
    {
        $theme = ThemeExtension::getTheme($theme);
        if(empty($theme)) {
           return null;
        }
        return new Theme($theme);
    }

    public static function isDefaultTheme(string $theme): bool
    {
        return ThemeExtension::isThemeActive($theme);
    }

    public static function themeSource(string $theme): ?string
    {
        $theme = ThemeExtension::getTheme($theme);
        return $theme['source_directory'] ?? null;
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

        $is_application_phone = getConfigValue('phone_app.silent_client');
        if($is_application_phone) {
            $file_script .= "<script type='text/javascript'>".file_get_contents('../core/default/themes/mini_cms/assets/js/application-silent.js'). '</script>';
        }

        $dom_content = $dom->saveHTML();

        // Add default assets.
        $dom_content = str_replace('{{DEFAULTS_ASSETS}}', $file_script ?? '', $dom_content);
        return $dom_content;
    }

    /**
     * @param string $section head or footer
     * @param string $asset
     * @return void
     */
    public function setAsset(string $section, string $asset): void
    {
        $this->assets[$section][] = $asset;
    }

    public static function build(string $view, $options = []): ?string
    {
        return Services::create('render')->render($view, $options);
    }

    public function getThemeName(): ?string
    {
        return $this->theme_name;
    }
}