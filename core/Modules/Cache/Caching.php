<?php

namespace Mini\Cms\Modules\Cache;

use Symfony\Component\Yaml\Yaml;

/**
 * @file Caching.php
 */

/**
 * @class Caching handles caches
 */
class Caching
{
    protected array $cache;

    protected string $cache_dir = "private://cache";

    public function __construct()
    {
        $this->cache = [];
        @mkdir($this->cache_dir, 0755, true);
        @file_put_contents($this->cache_dir . "/.htaccess", "Deny from all");
    }

    /**
     * @param string $key key for cache tag
     * @param mixed $value data to cache
     * @param int $write_flag FLAG 1 overwrite, 2 append
     * @return bool
     */
    public function set(string $key, array $value, int $write_flag = 1): bool
    {
        $key = clean_string_advance($key);
        $cache_file = $this->cache_dir . "/{$key}.yml";
        if (!file_exists($cache_file)) {
            touch($cache_file);
        }

        if(!file_exists($cache_file)) {
            return false;
        }
        $content = Yaml::parseFile($cache_file);
        if($write_flag === 1) {
            $content = Yaml::dump($value);
            return !empty(file_put_contents($cache_file, $content));
        }
        elseif ($write_flag === 2) {
            $content = array_merge($content, $value);
            $content = Yaml::dump($content);
            return !empty(file_put_contents($cache_file, $content));
        }
        return false;
    }

    /**
     * @param string $key key of cache tag
     * @return false|mixed data of tag has data.
     */
    public function get(string $key): array|bool
    {
        $key = clean_string_advance($key);
        $cache_file = $this->cache_dir . "/{$key}.yml";
        if (!file_exists($cache_file)) {
            return false;
        }
        return Yaml::parseFile($cache_file);
    }

    /**
     * Is cache tag available.
     * @param string $key
     * @return bool
     */
    public function is_exists(string $key): bool
    {
        $cache_file = $this->cache_dir . "/{$key}.yml";
        return file_exists($cache_file);
    }

    /**
     * Delete cache tag.
     * @param string $key
     * @return bool
     */
    public function delete(string $key): bool
    {
        $cache_file = $this->cache_dir . "/{$key}.yml";
        if (file_exists($cache_file)) {
            return unlink($cache_file);
        }
        return false;
    }

    /**
     * Load all cache data.
     * @return array
     */
    public function getAll(): array
    {
        $list_files = scandir($this->cache_dir);
        $list_files = array_diff($list_files, [".", ".."]);
        $cache_files = [];
        foreach ($list_files as $file) {
            $tag = explode(".", $file);
            $tag = $tag[0];
            $cache_files[$tag] = Yaml::parseFile($this->cache_dir . "/" . $file);
        }
        return $cache_files;
    }

    /**
     * Clear all
     * @return bool
     */
    public function clear(): bool
    {
        $list_files = scandir($this->cache_dir);
        $list_files = array_diff($list_files, [".", ".."]);
        foreach ($list_files as $file) {
            $file = $this->cache_dir . "/" . $file;
            if (file_exists($file)) {
                unlink($file);
            }
        }
        return true;
    }

    public static function cache(): Caching
    {
        return new self();
    }
}