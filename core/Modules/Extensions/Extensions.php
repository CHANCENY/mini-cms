<?php

namespace Mini\Cms\Modules\Extensions;

use Mini\Cms\Connections\Database\Database;
use Mini\Cms\Modules\Extensions\ModuleHandler\ModuleHandler;
use Mini\Cms\Modules\FileSystem\File;
use Mini\Cms\Modules\FileSystem\FileSystem;
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
        $database = new Database();
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
                    if(str_ends_with($file, '.info.json')){
                        $info_file = $unzipping_path . '/'. $file;
                        break;
                    }
                }

                if($info_file && file_exists($info_file)){
                    $info = json_decode(file_get_contents($info_file), true);
                    $name = $info['name'] ?? null;
                    if($name) {
                        $module_path_real = 'module://contrib/'.$name;
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
        return array_map(function($module) {
            return new ModuleHandler($module['ext_id']);
        },
            Database::database()->query("SELECT ext_id FROM `extensions` WHERE `ext_type` = 'module' AND ext_status = 'on'")->fetchAll());
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

}