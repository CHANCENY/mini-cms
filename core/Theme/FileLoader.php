<?php

namespace Mini\Cms\Theme;

use Mini\Cms\Configurations\ConfigFactory;
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

    public function findFiles(string $fileName): array
    {
        $foundFiles = [];
        return $this->recursive_finder($this->path, $fileName);
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
            if(is_file($fullPath) && str_ends_with($fullPath,$file_name)){
                $foundFiles[] = $fullPath;
            }
        }
        return $foundFiles;
    }

    public static function find(string $filename,string $path = ''): array
    {
        return (new FileLoader($path))->findFiles($filename);
    }

}