<?php

namespace Mini\Cms\Modules\Themes;

use Symfony\Component\Yaml\Yaml;
use ZipArchive;

class ThemeExtension
{
   
    public static function prepareThemeInstall(string $zip_theme_path): ?bool
    {
        // Unzipping the zip file
        $extract_to_path = 'private://theme/tmp/'.time();
        @mkdir($extract_to_path);

        $zip = new ZipArchive;
        if ($zip->open($zip_theme_path) === TRUE) {
            $zip->extractTo($extract_to_path);
            $zip->close();
        } else {
           return false;
        }

        // Validate the unzipped look for .info and __theme_libraries.yml file
        $files = array_diff(scandir($extract_to_path), ['..', '.']);
        $info_file = null;
        $theme_file = null;

        foreach ($files as $file) {
            if(str_ends_with($file, '.info.yml')) {
                $info_file = $extract_to_path . '/' . $file;
            }
        }
        if (empty($info_file)) {
            return false;
        }
        if(!file_exists($info_file)) {
            return false;
        }
        $content = Yaml::parseFile($info_file);
        if(is_array($content) && !empty($content['name']) && !empty($content['title']) && !empty($content['version'])) {

            $theme_name = $content['name'];
            $theme_file = $extract_to_path . '/src/__theme_libraries.yml';
            if(!file_exists($theme_file)) {
                return false;
            }
            $content = Yaml::parseFile($theme_file);
            if(is_array($content) && isset($content['head']) && isset($content['footer'])) {

                // installing here
                $installable_path = "theme://".$theme_name;
                $content = Yaml::parseFile($info_file);
                $content['source_directory'] = $installable_path ."/src";
                $content['installed_on'] = time();
                @mkdir($installable_path);
                $zip = new ZipArchive;
                if ($zip->open($zip_theme_path) === TRUE) {
                    $zip->extractTo($installable_path);
                    $zip->close();

                    file_put_contents($installable_path."/".$theme_name.".info.yml", $content);
                    self::clearDirectory($extract_to_path);
                    return true;
                } else {
                    return false;
                }
            }
        }
        return false;
    }

    private static function clearDirectory($dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = scandir($dir);
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                $filePath = $dir . DIRECTORY_SEPARATOR . $file;
                if (is_dir($filePath)) {
                    self::clearDirectory($filePath);  // Recursive call to clear subdirectories
                    rmdir($filePath);  // Remove the subdirectory itself
                } else {
                    unlink($filePath);  // Remove the file
                }
            }
        }
    }

    public static function createTheme(string $title, string $version, string $description): bool
    {
        $theme_name = strtolower(clean_string(clean_string_advance($title), '-', '_'));
        $theme_dir = "theme://$theme_name";
        if(is_dir($theme_dir)) {
            return false;
        }

        @mkdir($theme_dir);
        $theme_source_dir = $theme_dir. "/src";
        @mkdir($theme_source_dir);

        $libraries = $theme_source_dir . "/__theme_libraries.yml";
        file_put_contents($libraries, Yaml::dump([
            "global" => [],
            "head" => [],
            'footer' => [],
        ]));

        $info_file = $theme_dir . "/{$theme_name}.info.yml";
        $data = [
            'name' => $theme_name,
            'title' => $title,
            'version' => $version,
            'description' => $description,
            'source_directory' => $theme_source_dir
        ];
        file_put_contents($info_file, Yaml::dump($data));
        return true;
    }
}