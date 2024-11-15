<?php

namespace Mini\Cms\Theme;

use Mini\Cms\Configurations\ConfigFactory;
use Mini\Cms\Modules\Cache\Caching;
use Mini\Cms\Services\Services;

class FileLoader
{
    private string $path;

    public function __construct(string $path = '')
    {
        if (empty($path)) {
            $this->path = $_SERVER['DOCUMENT_ROOT'];
        }else {
            $this->path = $path;
        }
    }

    public function cacheTag(): string
    {
        $tag = clean_string($this->path,DIRECTORY_SEPARATOR,'_');
        $tag = trim($tag,'.');
        return trim($tag,'_');
    }

    public function findFiles(string $fileName): array
    {
        return $this->recursive_finder($this->path, $fileName);
    }

    public function allFiles(): array
    {
        return $this->recursive_loader($this->path);
    }

    private function recursive_finder(string $dir, string $file_name): array
    {
        $foundFiles = [];
        $fileInDirectory = array_diff(scandir($dir), ['..', '.']);
        foreach ($fileInDirectory as $file) {
            $fullPath = $dir . '/'. $file;
            if (is_dir($fullPath)) {
                $results = $this->recursive_finder($fullPath, $file_name);
                if(!empty($results)) {
                    $foundFiles = array_merge($foundFiles, $results);
                }
            }
            if(is_file($fullPath) && $file === $file_name){
                $foundFiles[] = $fullPath;
            }
        }
        return $foundFiles;
    }

    private function recursive_loader(string $dir): array
    {
        $foundFiles = [];
        $fileInDirectory = array_diff(scandir($dir), ['..', '.']);
        foreach ($fileInDirectory as $file) {
            $fullPath = $dir . '/'. $file;
            if (is_dir($fullPath)) {
                $results = $this->recursive_loader($fullPath);
                if(!empty($results)) {
                    $foundFiles = array_merge($foundFiles, $results);
                }
            }

            if(is_file($fullPath)){
                $foundFiles[] = $fullPath;
            }
        }
        return $foundFiles;
    }

    public static function find(string $filename,string $path = ''): array
    {
        return (new FileLoader($path))->findFiles($filename);
    }

    public static function all(string $path): array {
        return (new FileLoader($path))->allFiles();
    }

}