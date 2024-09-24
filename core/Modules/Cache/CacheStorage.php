<?php

namespace Mini\Cms\Modules\Cache;

use Mini\Cms\Connections\Database\Database;
use Mini\Cms\Connections\Database\Queries\QueryManager;
use Mini\Cms\Modules\CurrentUser\CurrentUser;
use Mini\Cms\Modules\Storage\Tempstore;
use Mini\Cms\Routing\RouteBuilder;

class CacheStorage
{
    const PERMANENT_CACHE = 'PERMANENT_CACHE';

    const TEMPORARY_CACHE = 'TEMPORARY_CACHE';

    const REPLACEABLE_CACHE = 'REPLACEABLE_CACHE';

    /**
     * Stream wrapper of root of cache directory.
     * @var string
     */
    private string $cache_storage = 'cache://';

    /**
     * Database center for caching.
     * @var string
     */
    private string $database_storage = 'caching';

    private array $settings = [];

    public function __construct()
    {
        if(!is_dir($this->cache_storage)){
            @mkdir($this->cache_storage);
            file_put_contents($this->cache_storage.'/.htaccess', "Deny from all");
        }
        $statement = Database::database()->prepare("CREATE TABLE IF NOT EXISTS $this->database_storage (cache_id SERIAL PRIMARY KEY, cache_tag VARCHAR(255), cache_type VARCHAR(255), cache_expire_time INT, cache_file_path VARCHAR(255));");
        $statement->execute();
        $this->settings = getConfigValue('caching_setting') ?? ['max_age' => 0, 'enabled' => 0];
    }

    /**
     * Returns root of cache storage directory
     * @return string
     */
    public function getCacheStorage(): string
    {
        return $this->cache_storage;
    }

    /**
     * Returns cached data.
     * @param string $cache_tag
     * @return string|null
     */
    public function get(string $cache_tag): ?string
    {
       try{
           $statement = Database::database()->prepare("SELECT cache_file_path FROM $this->database_storage WHERE cache_tag = :cache_tag");
           $statement->execute([':cache_tag' => $cache_tag]);
           $result = $statement->fetch();
           $file = reset($result);
           $content = file_get_contents($file);
           return unserialize(json_decode(base64_decode($content)));
       }catch (\Throwable) {
           return null;
       }
    }

    /**
     * Save the cache data.
     * @param string $cache_tag Cache tag.
     * @param mixed $cache_value Data to cache.
     * @param string $cache_type Cache type.
     * @param int $cache_expire_time
     * @return void
     * @throws \Exception
     */
    public function set(string $cache_tag, mixed $cache_value, string $cache_type = CacheStorage::PERMANENT_CACHE, int $cache_expire_time = 0): void
    {
        if(!$this->getCachingEnabled()) {
            return;
        }
        $cache_file = $this->cache_storage . $cache_tag . '.txt';
        if(!file_exists($cache_file)){
            @touch($cache_file);
        }
        if($this->get($cache_tag)) {

            if($cache_type !== CacheStorage::REPLACEABLE_CACHE) {
                return;
            }
            else {
                $this->clear($cache_tag);
            }

        }
        $process_cache = base64_encode(json_encode(serialize($cache_value)));
        $queryManager = new QueryManager(Database::database());
        $queryManager->insert($this->database_storage);
        $result = null;
        $data = [
            'cache_tag' => $cache_tag,
            'cache_expire_time' => $cache_expire_time,
            'cache_file_path' => $cache_file,
            'cache_type' => $cache_type
        ];

        if($cache_type === CacheStorage::TEMPORARY_CACHE){
            $temp_time = time() + (60 * 60 * 2);
            $data['cache_expire_time'] = $temp_time;
            foreach ($data as $key => $value) {
                $queryManager->addField($key, $value);
            }
            $result = $queryManager->execute();
        }
        else if($cache_type === CacheStorage::PERMANENT_CACHE){
            $time_cache = $cache_expire_time != 0 ? $cache_expire_time : -1;
            $data['cache_expire_time'] = $time_cache;
            foreach ($data as $key => $value) {
                $queryManager->addField($key, $value);
            }
            $result = $queryManager->execute();
        }

        if($result) {
            file_put_contents($cache_file, $process_cache);
        }
    }

    /**
     * Generate cache_tag
     * @return string
     */
    public function createCacheTag(): string
    {
        $current_route = Tempstore::load('current_route');

        // Handle if the route is not loaded
        if (!$current_route || !$current_route->getLoadedRoute()) {
            return 'anonymous|anonymous';
        }

        /** @var $current_route \Mini\Cms\Routing\Route */
        $current_route = $current_route->getLoadedRoute();
        $route_id = $current_route->getRouteId();

        // Dependency Injection would be ideal here for CurrentUser
        $current_user = new CurrentUser();
        $current_user_id = $current_user->id() ?? 'anonymous';

        return hash('sha256', $route_id . '|' . $current_user_id);
    }

    /**
     * Clear the cache on tag
     * @param string $cache_tag
     * @return bool
     */
    public function clear(string $cache_tag): bool
    {
        $cache_file = $this->cache_storage . $cache_tag . '.txt';
        if(file_exists($cache_file)){
            unlink($cache_file);
        }
        $st = Database::database()->prepare("DELETE FROM $this->database_storage WHERE cache_tag = :cache_tag");
        $st->execute([':cache_tag' => $cache_tag]);
        return true;
    }

    /**
     * Clear all cache on user tags.
     * @return void
     */
    public function purgeAll(): void
    {
        $route_builder = new RouteBuilder();
        $routes = $route_builder->getRoutes();
        $current_id = (new CurrentUser())->id() ?? 'anonymous';
        foreach ($routes as $route) {
            $cache_tag = $route->getRouteId().'|'.$current_id;
            $cache_tag = hash('sha256', $cache_tag);
            $this->clear($cache_tag);
        }
    }

    /**
     * Clear all cached data.
     * @return void
     */
    public function destroy(): void
    {
        Database::database()->query("TRUNCATE TABLE $this->database_storage");
        $list = array_diff(scandir($this->cache_storage), ['..', '.']);
        foreach ($list as $file) {
            unlink($this->cache_storage . $file);
        }
    }

    public function getSettingMaxAge(): int
    {
        return $this->settings['max_age'] ?? 0;
    }

    public function getCachingEnabled(): bool
    {
        return $this->settings['enabled'] ?? 0;
    }
}