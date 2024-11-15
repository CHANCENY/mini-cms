<?php

namespace Mini\Cms\Modules\Themes;

use Mini\Cms\Connections\Database\Database;
use Mini\Cms\Mini;
use Mini\Cms\Modules\Cache\Caching;
use Mini\Cms\Modules\ErrorSystem;
use Mini\Cms\Theme\FileLoader;
use PDO;
use Symfony\Component\Yaml\Yaml;
use Throwable;
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

    public static function bootThemes(): array
    {
        $themes_location = "theme://";
        if(!is_dir($themes_location)) {
            return [];
        }

        $admin_theme = $themes_location . "default_admin";
        if(!is_dir($admin_theme)) {
            @mkdir($admin_theme);
            $info_file = $admin_theme . "/default_admin.info.yml";
            $content = [
                'name' => 'default_admin',
                'title' => 'Mini Cms Admin',
                'version' => '1.0.0',
                'description' => 'This is the default admin theme.',
                'source_directory' => '../core/default/themes/mini_cms/src'
            ];
            file_put_contents($info_file, Yaml::dump($content));
        }

        $files = array_diff(scandir($themes_location), ['..', '.']);
        $themes = [];
        foreach ($files as $file) {
            $theme_directory = $themes_location . $file;
            if(is_dir($theme_directory)) {
                $info_file = $theme_directory .'/'. $file.".info.yml";
                if(file_exists($info_file)) {
                    $content = Yaml::parseFile($info_file);
                    // Quick validation
                    if(is_array($content) && isset($content['name']) && isset($content['title']) && !empty($content['source_directory']) && !empty($content['version'])) {
                        $themes[] = $content;
                    }
                }
            }
        }

        if(!empty($themes)) {
            Caching::cache()->set('system-themes', $themes);
        }
        return $themes;
    }

    private static function storage(): void {

        $database = new Database(true);
        if($database->getDatabaseType() == 'mysql') {
            $query = "CREATE DATABASE IF NOT EXISTS themes_register (thid int(11) NOT NULL AUTO_INCREMENT, PRIMARY KEY, 
            name varchar(255) NOT NULL, version varchar(255) NOT NULL, 
            title varchar(255) NOT NULL, 
            description varchar(255) NOT NULL, source_directory varchar(255) NOT NULL, active int(11) NOT NULL DEFAULT '0')";
        }
        elseif ($database->getDatabaseType() == 'sqlite') {
            $query = "CREATE TABLE IF NOT EXISTS themes_register (thid INTEGER PRIMARY KEY AUTOINCREMENT, 
                      name TEXT NOT NULL, 
                      version TEXT NOT NULL, 
                      title TEXT NOT NULL, 
                      description TEXT NOT NULL, 
                      source_directory TEXT NOT NULL, active INTEGER NOT NULL DEFAULT '0')";
        }
        $query = $database->connect()->prepare($query);
        $query->execute();
    }

    public static function enableTheme(string $theme_name, int $status): bool
    {
        try{
            self::storage();
            Mini::connection()->exec("UPDATE themes_register SET `active` = 0;");
            $query = "UPDATE `themes_register` SET `active` = :active WHERE `name` = :name;";
            $query = Mini::connection()->prepare($query);
            $query->bindParam(':active', $status);
            $query->bindParam(':name', $theme_name);
            return $query->execute();
        }catch (Throwable $e) {
            (new ErrorSystem())->setException($e)->save();
        }
        return false;
    }

    public static function isThemeActive(string $theme_name): bool
    {
        try{
            self::storage();
            $query = Mini::connection()->prepare("SELECT * FROM themes_register WHERE name = :name AND active = 1");
            $query->bindParam(':name', $theme_name);
            $query->execute();
            return !empty($query->fetch());
        }catch (Throwable $e) {
            (new ErrorSystem())->setException($e)->save();
        }
        return false;
    }


    public static function getTheme(string $theme_name): ?array
    {
       $themes = self::bootThemes();
       $found = array_filter($themes, function($theme) use ($theme_name) {
           return $theme['name'] === $theme_name;
       });
       return !empty($found) ? reset($found) : null;
    }

    public static function rebuildThemes(): bool
    {
        $themes = self::bootThemes();

        if(empty($themes)) {
            return false;
        }
        $caching = new Caching();
        foreach ($themes as $theme) {
            $source_directory = $theme['source_directory'];
            $files = FileLoader::all($source_directory);
            if(!empty($files)) {
                $tag = $theme['name'].'-'.$theme['version'];
                $caching->set($tag, $files);
            }
        }
        try{
            self::storage();
            foreach ($themes as $theme) {
                $query = Mini::connection()->prepare("SELECT * FROM themes_register WHERE name = :name");
                $query->bindParam(':name', $theme['name']);
                $query->execute();
                $data = $query->fetch(PDO::FETCH_ASSOC);
                if(empty($data)) {
                   $query = Mini::connection()->prepare("INSERT INTO themes_register (name, version, title, description, source_directory) VALUES (:name, :version,  :title, :description, :source_directory)");
                   $query->bindParam(':name', $theme['name']);
                   $query->bindParam(':version', $theme['version']);
                   $query->bindParam(':title', $theme['title']);
                   $query->bindParam(':description', $theme['description']);
                   $query->bindParam(':source_directory', $theme['source_directory']);
                   $query->execute();
                }
            }
        }catch (Throwable $e) {
            (new ErrorSystem())->setException($e)->save();
        }
        return true;
    }

    public static function getActiveTheme(): array
    {
        try{
            self::storage();
            $query = Mini::connection()->prepare("SELECT * FROM themes_register WHERE active = 1");
            $query->execute();
            $data = $query->fetch(PDO::FETCH_ASSOC);
            if(empty($data)) {
                return self::getTheme('default_admin');
            }
            return $data;
        }catch (Throwable $e) {
            (new ErrorSystem())->setException($e)->save();
        }
        return [];
    }

    public static function getThemes(): array
    {
        try{
            self::storage();
            $query = Mini::connection()->prepare("SELECT * FROM themes_register");
            $query->execute();
            $data = $query->fetchAll(PDO::FETCH_ASSOC);
            return $data;
        }catch (Throwable $e) {
            (new ErrorSystem())->setException($e)->save();
        }
        return [];
    }

    public static function getDefaultTheme(string $theme_name): array
    {
        $themes = self::bootThemes();
        $found = array_filter($themes, function($theme) use ($theme_name) {
            return $theme['name'] === $theme_name;
        });
        return !empty($found) ? reset($found) : [];
    }

}