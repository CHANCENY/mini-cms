<?php

namespace Mini\Cms\Modules\Extensions;

use Mini\Cms\Connections\Database\Database;
use Mini\Cms\Mini;
use Mini\Cms\Modules\Cache\Caching;
use Mini\Cms\Modules\ErrorSystem;
use Mini\Cms\Modules\Extensions\ModuleHandler\ModuleHandler;
use Mini\Cms\Modules\FileSystem\File;
use Mini\Cms\Modules\FileSystem\FileSystem;
use Symfony\Component\Yaml\Yaml;
use Throwable;
use ZipArchive;

/**
 *
 */
final class Extensions
{
    /**
     * Schema creator.
     * @return void
     */
    public static function extensionsStorage(): void
    {
        $database = new Database(true);
        if($database->getDatabaseType() === 'mysql') {
            $query_line = "CREATE TABLE IF NOT EXISTS `extensions` (ext_id INT(11) PRIMARY KEY AUTO_INCREMENT, ext_name VARCHAR(255) NOT NULL, ext_version VARCHAR(255), ext_status VARCHAR(255), ext_type VARCHAR(255) NOT NULL, ext_path VARCHAR(500) NOT NULL)";
           $database->connect()->query($query_line)->execute();
        }

        if($database->getDatabaseType() === 'sqlite') {
            $query_line = "CREATE TABLE IF NOT EXISTS `extensions` (ext_id INTEGER PRIMARY KEY AUTOINCREMENT, ext_name VARCHAR(255) NOT NULL, ext_version VARCHAR(255), ext_status VARCHAR(255), ext_type VARCHAR(255) NOT NULL, ext_path VARCHAR(500) NOT NULL)";
            $database->connect()->query($query_line)->execute();
        }
    }

    /**
     * Prepare installation of module.
     * @param int $fid
     * @return string|null
     */
    public static function extensionsPrepareModule(int $fid): string|null
    {
        $unzipping_path = 'public://extensions';
        if(!is_dir($unzipping_path)) {
            mkdir($unzipping_path);
        }

        $fileObject = File::load($fid);
        if($fileObject) {
            $module_path = $fileObject->getFilePath();

            // Initialize the ZipArchive class
            $zip = new ZipArchive;

            // Open the zip file
            if ($zip->open($module_path) === TRUE) {
                // Extract contents to the specified directory
                $zip->extractTo($unzipping_path);
                // Close the zip file
                $zip->close();

                $files = array_diff(scandir($unzipping_path), ['..', '.']);
                $info_file = null;
                foreach ($files as $file) {
                    if(str_ends_with($file, '.info.yml')){
                        $info_file = $unzipping_path . DIRECTORY_SEPARATOR . $file;
                        break;
                    }
                }

                if($info_file && file_exists($info_file)){
                    $info = Yaml::parseFile($info_file);
                    $name = $info['name'] ?? null;
                    if($name) {
                        $module_path_real = 'module://contrib'. DIRECTORY_SEPARATOR .$name;
                        if(!is_dir($module_path_real)) {
                            mkdir($module_path_real);

                            // Initialize the ZipArchive class
                            $zip = new ZipArchive;
                            if ($zip->open($module_path) === TRUE) {
                                // Extract contents to the specified directory
                                $zip->extractTo($module_path_real);
                                // Close the zip file
                                $zip->close();

                                $fileObject->delete();
                                FileSystem::removeDirectory($unzipping_path);
                                return $module_path_real;
                            }
                        }
                    }
                }
            }
        }
        return null;
    }

    /**
     * Loading all modules in a system.
     * @return array
     */
    public static function loadModules(): array {

        return array_map(function($module) {
            return new ModuleHandler($module['ext_id']);
        },
            Database::database()->query("SELECT ext_id FROM `extensions` WHERE `ext_type` = 'module'")->fetchAll());
    }

    /**
     * Save extension states.
     * @param array $extensions
     * @return bool
     */
    public static function saveExtensions(array $extensions): bool
    {
        $flag = [];
        foreach ($extensions as $extension_id=>$extension_Status) {
            $query = Database::database()->prepare("UPDATE extensions SET `ext_status` = :status WHERE `ext_id` = :id");
            $flag[] = $query->execute(['status' => $extension_Status, 'id' => $extension_id]);

        }
        return in_array(true, $flag);
    }

    /**
     * Get all active modules.
     * @return array
     */
    public static function activeModules(): array
    {
        self::extensionsStorage();
        $modules = Database::database()->query("SELECT ext_id FROM `extensions` WHERE `ext_type` = 'module' AND ext_status = 'on'")->fetchAll();
        try{
            return array_map(function ($module) {
                return new ModuleHandler($module['ext_id']);
            }, $modules);
        }catch (Throwable $exception){
            (new ErrorSystem())->setException($exception);
            return [];
        }
    }


    public static function runHooks(string $hook_name, array $args = []): void
    {
        self::extensionsStorage();
        $modules = self::activeModules();
        
        if(!empty($modules)) {
            foreach ($modules as $module) {
                if($module instanceof ModuleHandler) {
                    $module_file = $module->getHooksFile();
                    $module_name = $module->getName(). $hook_name;
                    if(file_exists($module_file)) {
                        require_once $module_file;
                        if(function_exists($module_name)) {
                            call_user_func_array($module_name,$args);
                        }
                    }
                }
            }
        }
    
    }

    public static function moduleInSystem(): array
    {
        $modules = self::attachModules();
        foreach ($modules as $module) {
            $query = Database::database()->prepare("SELECT ext_id FROM `extensions` WHERE `ext_name` = :name");
            $query->execute(['name' =>trim( $module['name'])]);
            $d = $query->fetch();
            if(empty($d)) {
                $query = Database::database()->prepare("INSERT INTO `extensions` (ext_name, ext_version, ext_status,ext_type, ext_path) VALUES(:name, :version, :status, :type, :path)");
                $query->execute([
                    'name' => $module['name'],
                    'version' => $module['version'],
                    'status' => 0,
                    'type' => $module['type'],
                    'path' => $module['path'],
                ]);
            }
        }
        return $modules;
    }

    // Recursive function to scan directories
    private static function scanDirectories($dir, &$infoFiles): void
    {
        // Open the directory
        $files = scandir($dir);

        // Loop through the directory contents
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $fullPath = $dir . DIRECTORY_SEPARATOR . $file;

            // If it's a directory, recursively scan it
            if (is_dir($fullPath)) {
                self::scanDirectories($fullPath, $infoFiles);
            }
            // If it's a file and ends with .info.json, add it to the array
            elseif (is_file($fullPath) && preg_match('/\.info\.yml$/', $file)) {
                $infoFiles[] = $fullPath;
            }
        }
    }

    public static function importRoutes(): array {
        $modules = self::activeModules();
        $routes = self::bootRoutes();
        foreach ($modules as $module) {
            if($module instanceof ModuleHandler) {
                $routes = array_merge($routes, $module->getModuleRoutes());
            }
        }
        return $routes;
    }

    public static function bootServices(): array
    {
        $modules = self::attachModules();
        if(empty($modules)) {
            return [];
        }
        $services = array();
        foreach ($modules as $module) {
            $service_path = ($module['path'] ?? '') . DIRECTORY_SEPARATOR . $module['name'] . '.services.api.yml';
            if(file_exists($service_path)) {
                $services = array_merge($services, Yaml::parseFile($service_path));
            }
        }

        if(!empty($services)) {
            Caching::cache()->set('system-services-register',$services);
        }
        return $services;
    }

    private static function attachModules(): array
    {
        $default_modules = [];
        if(is_dir('default://')) {
            self::scanDirectories('default://', $default_modules);
        }

        $contrib_modules = [];
        if(is_dir('module://contrib')) {
            self::scanDirectories('module://contrib', $contrib_modules);
        }

        $custom_modules = [];
        if(is_dir('module://custom')) {
            self::scanDirectories('module://custom', $custom_modules);
        }

        $modules = array();
        if(!empty($default_modules)) {
            foreach ($default_modules as $module) {
                $module_path = substr($module, 0, strrpos($module, DIRECTORY_SEPARATOR));
                $file_content = Yaml::parseFile($module);
                $modules[] = array_merge($file_content, ['path' => $module_path]);
            }
        }

        if(!empty($custom_modules)) {
            foreach ($custom_modules as $module) {
                $module_path = substr($module, 0, strrpos($module, DIRECTORY_SEPARATOR));
                $file_content = Yaml::parseFile($module);
                $modules[] = array_merge($file_content, ['path' => $module_path]);
            }
        }

        if(!empty($contrib_modules)) {
            foreach ($contrib_modules as $module) {
                $module_path = substr($module, 0, strrpos($module, DIRECTORY_SEPARATOR));
                $file_content = Yaml::parseFile($module);
                $modules[] = array_merge($file_content, ['path' => $module_path]);
            }
        }

        return $modules;
    }

    public static function bootRoutes(): array
    {
        $modules = self::attachModules();
        if(!empty($modules)) {
            $routes_in_modules = [];
            foreach ($modules as $module) {

                if(isset($module['auto_installable']) && $module['auto_installable'] === true)
                {
                    $path = str_starts_with($module['path'], 'default://') ? $module['path'] : null;
                    if($path) {
                        $route_path = trim($module['path'], '/') . DIRECTORY_SEPARATOR . $module['name'] . '.routing.yml';
                        if(file_exists($route_path)) {
                            $routes = Yaml::parseFile($route_path);
                            $routes_in_modules = array_merge($routes_in_modules, $routes);
                        }
                    }
                }
            }
            return $routes_in_modules;
        }
        return [];
    }

    public static function bootMenus(): array
    {
        $modules = self::attachModules();
        if(!empty($modules)) {
            $menus_in_modules = [];
            foreach ($modules as $module) {

                if(isset($module['auto_installable']) && $module['auto_installable'] === true)
                {
                    $path = str_starts_with($module['path'], 'default://') ? $module['path'] : null;
                    if($path) {
                        $menus_path = trim($module['path'], '/') . DIRECTORY_SEPARATOR . $module['name'] . '.menus.yml';
                        if(file_exists($menus_path)) {
                            $routes = Yaml::parseFile($menus_path);
                            $menus_in_modules = array_merge($menus_in_modules, $routes);
                        }
                    }
                }
            }
            return $menus_in_modules;
        }
        return [];
    }
}