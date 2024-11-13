<?php

namespace Mini\Cms\StorageManager;

use Mini\Cms\Connections\Database\Database;
use Mini\Cms\Modules\Extensions\Extensions;
use PDO;
use PDOException;

class Connector
{
    private PDO $connection;

    private string $connectionType = 'sqlite';

    public function getConnectionType(): string
    {
        return $this->connectionType;
    }
    public function __construct(string $override_sqlite_path = null, mixed $external_connection = null)
    {
        // Definitions database
        $db_file = $override_sqlite_path ??  __DIR__ . "/definitions_storage.db";

        // Connecting database
        try{
            if($external_connection) {
                $this->connection = $external_connection;
                $this->connectionType = 'others';
            }
            else {
                $pdo = new PDO('sqlite:'.$db_file);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                $this->connection = $pdo;
            }
            $this->createTables();
        }catch (PDOException $e){
            die ('DB Error'. $e->getMessage());
        }

    }

    public function getConnection(): PDO
    {
        return $this->connection;
    }

    public static function connect(string $override_sqlite_path = null, mixed $external_connection = null): Connector
    {
        return (new Connector($override_sqlite_path, $external_connection));
    }

    private function createTables(): void
    {
        $database = new Database(true);

        if($database->getDatabaseType() === 'sqlite') {
            $query = "CREATE TABLE IF NOT EXISTS `node_field_data` ( `nid` INTEGER PRIMARY KEY AUTOINCREMENT, `vid` INTEGER UNSIGNED NOT NULL, `type` varchar(32) NOT NULL, `langcode` varchar(12) NOT NULL, `status` tinyint(4) NOT NULL, `uid` INTEGER UNSIGNED NOT NULL, `title` varchar(255) NOT NULL, `created` INTEGER NOT NULL, `changed` int(11) NOT NULL )";
            $this->connection->query($query);
            $query = "CREATE TABLE IF NOT EXISTS `address_fields_data` (lid INTEGER PRIMARY KEY AUTOINCREMENT, country_code varchar(20) NOT NULL, state_code varchar(20) NULL, city_id varchar(11) NULL, zip_code varchar(20), address_1 varchar(255), address_2 varchar(255), county varchar(255))";
            $this->connection->exec($query);
        }
        if($database->getDatabaseType() === 'mysql') {
            $query = "CREATE TABLE IF NOT EXISTS `node_field_data` ( `nid` int(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, `vid` int(10) UNSIGNED NOT NULL, `type` varchar(32) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL COMMENT 'The ID of the target entity.', `langcode` varchar(12) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL, `status` tinyint(4) NOT NULL, `uid` int(10) UNSIGNED NOT NULL COMMENT 'The ID of the target entity.', `title` varchar(255) NOT NULL, `created` int(11) NOT NULL, `changed` int(11) NOT NULL ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='The data table for node entities.'";
            $this->connection->query($query);
            $query = "CREATE TABLE IF NOT EXISTS `address_fields_data` (lid INT(11) PRIMARY KEY AUTO_INCREMENT, country_code varchar(20) NOT NULL, state_code varchar(20) NULL, city_id varchar(11) NULL, zip_code varchar(20), address_1 varchar(255), address_2 varchar(255), county varchar(255))";
            $this->connection->exec($query);
        }
        Extensions::extensionsStorage();
    }

}