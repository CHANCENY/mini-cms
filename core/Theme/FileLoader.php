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

    private function cacheTag(): string
    {
        $tag = clean_string($this->path,DIRECTORY_SEPARATOR,'_');
        $tag = trim($tag,'.');
        return trim($tag,'_');
    }

    public function findFiles(string $fileName): array
    {

        $foundFiles = [];
        if(!Caching::cache()->is_exists($this->cacheTag())) {
            Caching::cache()->set($this->cacheTag(),array());
            return $this->recursive_finder($this->path, $fileName);
        }
        else {
            $files = Caching::cache()->get($this->cacheTag());
            foreach ($files as $file) {
                $list = explode(DIRECTORY_SEPARATOR,$file);
                if(is_file($file) && trim(end($list)) == $fileName){
                    $foundFiles[] = $file;
                }
            }
        }
        return $foundFiles;
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
            if(is_file($fullPath)) {
                Caching::cache()->set($this->cacheTag(), [$fullPath], 2);
            }
            if(is_file($fullPath) && $file === $file_name){
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