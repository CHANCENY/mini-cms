<?php

namespace Mini\Cms\Connections\Socket;

use Mini\Cms\Configurations\ConfigFactory;
use Mini\Cms\default\modules\default\core\src\Controllers\CacheSettings;
use Mini\Cms\Modules\Cache\Caching;

class SocketConnector
{

    public function addConfigValue(string $host, int $port, string $key, string $title)
    {
        $config = new ConfigFactory();
        $old = $config->get('Socket') ?? [];
        $old[$key] = [
            'host' => $host,
            'port' => $port,
            'key' => $key,
            'title' => $title
        ];
        $config->set('Socket', $old);
        return $config->save();
    }

    public function getConfig(string $key): ?array
    {
        $config = new ConfigFactory();
        return $config->get('Socket')[$key] ?? null;
    }

    /**
     * Make Socket object.
     * @param string $key Configuration key where to look for host and post in configuration.yml file under Socket
     * @return AbstractSocket If config found Socket is return where you need to run run() method.
     * @throws SocketConfigKeyNotFound
     */
    public function connect(string $key): AbstractSocket
    {
        $config = $this->getConfig($key);
        if(!empty($config) && is_array($config) && !empty($config['host']) && !empty($config['port'])) {
            return $socket = new AbstractSocket($config['host'], $config['port']);
        }
        throw new SocketConfigKeyNotFound("$key is not a valid socket configuration key");
    }

    public static function create()
    {
        return new SocketConnector();
    }

    public function getConfigs(): ?array
    {
        $config = new ConfigFactory();
        return $config->get('Socket');
    }

    public function httpRunSocket($key, string $background_script_file): void
    {
        $config = $this->getConfig($key);
        if(!empty($config) && is_array($config) && !empty($config['host']) && !empty($config['port'])) {
            $back_grounds = Caching::cache()->get('backgrounds-processes');
            if(!empty($back_grounds) && in_array($key, array_keys($back_grounds))) {
                return;
            }
            $hostname = $config['host'] . ':' . $config['port'];
            $socket_logs = __DIR__ .'/../../../configs/socket';
            @mkdir($socket_logs);
            $socket_logs .= "/error.log";
            @file_put_contents($socket_logs, "Attempting to connect to {$config['host']}:{$config['port']}");
            $socket_logs = realpath($socket_logs);
            $command = "php $background_script_file '$hostname' > $socket_logs 2>&1 & echo $!";
            $pid = shell_exec($command);
            if(!empty($pid)) {
                $pid = trim($pid);
                $config['pid'] = $pid;
                $back_grounds[$key] = $config;
                Caching::cache()->set('backgrounds-processes', $back_grounds);
            }
        }
    }
}