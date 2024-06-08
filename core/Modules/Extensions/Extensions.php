<?php

namespace Mini\Cms\Modules\Extensions;

use Mini\Cms\Connections\Database\Database;
use Mini\Cms\Modules\FileSystem\File;
use Mini\Cms\Modules\FileSystem\FileSystem;
use ZipArchive;

final class Extensions
{
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

}