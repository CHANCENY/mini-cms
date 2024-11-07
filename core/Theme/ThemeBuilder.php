<?php

namespace Mini\Cms\Theme;

use Exception;
use Symfony\Component\Yaml\Yaml;
use Mini\Cms\Connections\Database\Database;
use Mini\Cms\Modules\FileSystem\File;

class ThemeBuilder {


    public function __construct(private readonly Database $con, private File $file, private array $theme){}

    public static function create(): array {
        return [
            'theme' => [
                'title' => null,
                'name' => null,
                'icon' => null,
                'description' => null,
                'source_directory' => null,
                'active' => false,
            ],
        ];
    }

    public function setTitle(string $title): void {
        $this->theme['title'] = $title;
        $this->theme['name'] = strtolower(clean_string(clean_string_advance($title), '-', '_'));
    }

    public function setIcon(int|string $icon): void {
        $this->theme['icon'] = $icon;
    }

    public function setActive(bool $active): void {
        $this->theme['active'] = $active;
    }

    public function setDescription(string $description): void {
        $this->theme['description'] = $description;
    }

    public function setSourceDirectory(string|null $source_dir = null): void {
        if($source_dir) {
            $this->theme['source_directory'] = 'theme://'. trim($source_dir, '/');
        }
        else {
            if(!empty($this->theme['name'])) {
                $this->theme['source_directory'] = 'theme://'. $this->theme['name'];
            }
        }
    }

    public function make(): bool
    {
        $flags = [];
        
        try{
            // Create theme directory.
            if (empty($this->theme['source_directory'])) {
                $this->setSourceDirectory();
            }
            @mkdir($this->theme['source_directory']);

            $flags['remove_dir'] = function () {
                rmdir($this->theme['source_directory']);
            };

            // Create source directory.
            $source_direcotry = $this->theme['source_directory'] . DIRECTORY_SEPARATOR . 'src';
            @mkdir($source_direcotry);
            $flags['remove_dir_source'] = function () use ($source_direcotry) {
                rmdir($source_direcotry);
            };

            //Create theme_libraries.yml file
            $content = Yaml::dump([
                'global' => [],
                'head' => [],
                'footer' => []
            ]);
            $theme_file = $source_direcotry . DIRECTORY_SEPARATOR . '__theme_libraries.yml';
            file_put_contents($theme_file, $content);
            $flags['remove_theme_file'] = function () use ($theme_file) {
                unlink($theme_file);
            };

            // Create icon image
            $icon = $this->theme['source_directory'] . DIRECTORY_SEPARATOR . 'icon';
            @mkdir($icon);
            $flags['remove_icon_dir'] = function() use($icon){ rmdir($icon); };

            $file = $this->file->file($this->theme['icon']);
            if($file) {
                $file_path = $file->getFilePath();
                $icon = $icon . DIRECTORY_SEPARATOR . $file->getName();
                if(file_put_contents($icon,file_get_contents($file_path))) {
                    $this->theme['icon'] = $icon;
                    $flags['remove_icon_image'] = function()use($icon){ unlink($icon); };
                }
            }
            // Create .info.yml file.
            $info = $this->theme['source_directory'] . DIRECTORY_SEPARATOR . $this->theme['name'] . '.info.yml';
            $this->theme['source_directory'] = $this->theme['source_directory'] . DIRECTORY_SEPARATOR . 'src';
            file_put_contents($info, Yaml::dump($this->theme));
            $flags['remove_info_file'] = function () use ($info) {
                unlink($info);
            };

            return true;
        }catch(\Throwable) {
            if($flags) {
                $flags = array_reverse($flags);
                foreach($flags as $key=>$value) {
                    $value();
                }
            }
            return false;
        }
    }
}