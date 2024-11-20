<?php

/**
 * Class response for making sure everything is set properly
 */
namespace Mini\Cms\bootstrap;

use Mini\Cms\App\App;
use Mini\Cms\Modules\Cache\Caching;
use Mini\Cms\Modules\ErrorSystem;
use Mini\Cms\Modules\Extensions\Extensions;
use Mini\Cms\Modules\Streams\MiniWrapper;
use Mini\Cms\Modules\Themes\ThemeExtension;
use Mini\Cms\System\System;
use Mini\Cms\Theme\Theme;
use Symfony\Component\Yaml\Yaml;

class Kernel extends System
{
    public string|array|int|null|false $path;

    public string $method;

    private App $app;

    public function __construct()
    {
        parent::__construct();
    }

    public function initializeDirectories(): void
    {
        $wrappers = $this->getWrapperRegistered();
        foreach ($wrappers as $wrapper) {
            if(!is_dir($wrapper))
                @mkdir($wrapper);
        }
    }

    public function initializeApplicationGlobals(): void
    {
        // Get configurations
        try{
            $config_path = trim($this->getAppConfigRoot(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'configurations.yml';
            $config_path = !file_exists($config_path) ? DIRECTORY_SEPARATOR. $config_path : $config_path;
            $configuration = Yaml::parseFile($config_path);
            if($configuration && is_array($configuration)) {

                $configuration = (object) $configuration;

                if(!empty($configuration->error_saver)) {
                    $error_saver = new ErrorSystem();
                    define_global('error_saver', $error_saver);
                }

                // So at this point to be safe, we need to check in cache if configurations are there if not
                // load them from default core modules. Since the site needs these to function

                $caching = Caching::cache();

                // Loading services.
                $services = $caching->get('system-services-register') ?? Extensions::bootServices();

                // Loading routes.
                $routes = $caching->get('system-routes') ?? Extensions::bootRoutes();

                // Loading menus
                $menus = $caching->get('system-menus') ?? Extensions::bootMenus();

                // Loading Themes
                $themes = $caching->get('system-themes') ?? ThemeExtension::bootThemes();

                // Setting globals
                define_global('routes', $routes);
                define_global('menus', $menus);
                define_global('services', $services);
                define_global('configuration', (array) $configuration);
                define_global('themes', $themes);
                define_global('mini_wrapper_class', new MiniWrapper());
                define_global('mini_speed_meter', time());
            }
        }catch (\Exception $e){
            die("unexpected error loading configurations");
        }
    }

    public function initializeApplication(): void
    {
        $error_handler_on = get_config_value('error_saver');
        if($error_handler_on)
        {
            set_error_handler("mini_cms_error_handler");
            set_exception_handler("mini_cms_exception_handler");
        }
    }

    public function kernelRequestInitialize(): void
    {
        $request_uri = $_SERVER['REQUEST_URI'];
        $this->path = parse_url($request_uri ?? '/',PHP_URL_PATH);
        $this->method = $_SERVER['REQUEST_METHOD'];
    }

    public function appStart(): void
    {
        // Cover error handling
        $this->app = new App($this->method, $this->path, new \Mini\Cms\Controller\Route());
    }

    public function terminate(): void
    {
        mini_php_shutdown_handler();
        unset($this->app);
        exit();
    }
}