<?php

namespace Mini\Cms\Connections\Database;

use Mini\Cms\Configurations\ConfigFactory;
use Mini\Cms\Modules\FileSystem\FileSystem;
use Mini\Cms\Services\Services;
use Mini\Cms\StorageManager\Connector;
use PDO;
use Symfony\Component\VarDumper\Exception\ThrowingCasterException;

class Database
{
    private ?array $database;

    private string $sqlite_file = '../configs/database';

    private \PDO $connection;

    /**
     * @throws \Exception
     */
    public function __construct(bool $reset= false)
    {
        global $database;
        if(!isset($database) || $reset === true) {
            $config = Services::create('config.factory');
            if($config instanceof ConfigFactory) {

                $this->database = $config->get('database');

                // Making connection to a sqlite database.
                if($this->getDatabaseType() === 'sqlite') {

                    if(!is_dir($this->sqlite_file)) {
                        mkdir($this->sqlite_file);
                    }

                    $alternative_path = '../../configs/database';
                    if(!is_dir($this->sqlite_file)) {
                        mkdir($alternative_path);
                        $this->sqlite_file =$alternative_path;
                    }

                    if(is_dir($this->sqlite_file)) {
                        // Make path to database file.
                        $this->sqlite_file = $this->sqlite_file .'/'. $this->getDatabaseName(). '.sqlite';
                        $this->connection = new PDO('sqlite:'.$this->sqlite_file);
                        $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                        $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                    }
                }

                // Making connection to mysql database.
                if($this->getDatabaseType() === 'mysql') {
                    $dsn = 'mysql:host='.$this->getDatabaseHost().';dbname=' . $this->getDatabaseName().';charset=utf8mb4';
                    $this->attemptConnection();
                    $this->connection = new PDO($dsn, $this->getDatabaseUser(), $this->getDatabasePassword());
                    $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                    $this->connection->setAttribute(PDO::ATTR_PERSISTENT,true);
                }
                $database = $this->connection;
            }
        }
        else {
            $this->connection = $database;
        }
    }

    public function getDatabaseType(): ?string
    {
        return $this->database['db_type'] ?? null;
    }

    public function getDatabase(): ?array
    {
        return $this->database;
    }

    public function getDatabaseName(): ?string
    {
        return $this->database['db_name'] ?? null;
    }

    public function getDatabaseHost(): ?string
    {
        return $this->database['db_host'] ?? null;
    }

    public function getDatabaseUser(): ?string
    {
        return $this->database['db_user'] ?? null;
    }

    public function getDatabasePassword(): ?string
    {
        return $this->database['db_password'] ?? null;
    }

    public function connect(): PDO|null
    {
        return $this->connection ?? null;
    }

    public static function database(): PDO|null
    {
        global $database;
        if(isset($database)) {
            return $database;
        }
        return (new Database())->connect();
    }

    private function attemptConnection(): void
    {
        try {
            $pdo = new PDO("mysql:host={$this->getDatabaseHost()}", $this->getDatabaseUser(), $this->getDatabasePassword());
            $pdo->exec("CREATE DATABASE IF NOT EXISTS {$this->getDatabaseName()}");
        }catch (\Throwable $throwable) {
            return;
        }
    }

    public function dbProcesses(): array {
        return $this->connection->query("SHOW PROCESSLIST")->fetchAll();
    }

}