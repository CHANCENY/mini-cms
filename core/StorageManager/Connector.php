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
        $database = new Database();

        if($database->getDatabaseType() === 'sqlite') {
            // Table entity_types.
            $query = "CREATE TABLE IF NOT EXISTS `entity_types` (entity_type_id INTEGER PRIMARY KEY AUTOINCREMENT, entity_type_name VARCHAR(255), entity_type_description VARCHAR(255), entity_label VARCHAR(255))";
            $this->connection->exec($query);

            // Table entity_types_fields.
            $query = "CREATE TABLE IF NOT EXISTS `entity_types_fields` (entity_type_field_id INTEGER PRIMARY KEY AUTOINCREMENT, field_name VARCHAR(255), field_description VARCHAR(255), field_label VARCHAR(255), field_type VARCHAR(255), field_settings TEXT NOT NULL, entity_type_id INTEGER NOT NULL)";
            $this->connection->exec($query);

            $query = "CREATE TABLE IF NOT EXISTS `entity_node_data` (node_id INTEGER PRIMARY KEY AUTOINCREMENT, bundle varchar(255), title varchar(400) NOT NULL, deleted boolean DEFAULT FALSE NOT NULL, created varchar(255) NOT NULL, updated varchar(255) NOT NULL, status varchar(255) NOT NULL, uid INTEGER NOT NULL)";
            $this->connection->exec($query);

            $query = "CREATE TABLE IF NOT EXISTS `vocabularies` (vid INTEGER PRIMARY KEY AUTOINCREMENT, vocabulary_name varchar(255), vocabulary_label vachar(255))";
            $this->connection->exec($query);

            $query = "CREATE TABLE IF NOT EXISTS `terms` (term_id INTEGER PRIMARY KEY AUTOINCREMENT, term_name varchar(255) NOT NULL, vocabulary_id INTEGER NOT NULL)";
            $this->connection->exec($query);

            $query = "CREATE TABLE IF NOT EXISTS `term_nodes` (tnd INTEGER PRIMARY KEY AUTOINCREMENT, tid INTEGER NOT NULL, nid INTEGER NOT NULL)";
            $this->connection->exec($query);

            $query = "CREATE TABLE IF NOT EXISTS `address_fields_data` (lid INTEGER PRIMARY KEY AUTOINCREMENT, country_code varchar(20) NOT NULL, state_code varchar(20) NULL, city_id varchar(11) NULL, zip_code varchar(20), address_1 varchar(255), address_2 varchar(255), county varchar(255))";
            $this->connection->exec($query);
        }
        if($database->getDatabaseType() === 'mysql') {
            // Table entity_types.
            $query = "CREATE TABLE IF NOT EXISTS `entity_types` (entity_type_id INTEGER PRIMARY KEY AUTO_INCREMENT, entity_type_name VARCHAR(255), entity_type_description VARCHAR(255), entity_label VARCHAR(255))";
            $this->connection->exec($query);

            // Table entity_types_fields.
            $query = "CREATE TABLE IF NOT EXISTS `entity_types_fields` (entity_type_field_id INTEGER PRIMARY KEY AUTO_INCREMENT, field_name VARCHAR(255), field_description VARCHAR(255), field_label VARCHAR(255), field_type VARCHAR(255), field_settings TEXT NOT NULL, entity_type_id INTEGER NOT NULL)";
            $this->connection->exec($query);

            $query = "CREATE TABLE IF NOT EXISTS `entity_node_data` (node_id INTEGER PRIMARY KEY AUTO_INCREMENT, bundle varchar(255), title varchar(400) NOT NULL, deleted boolean DEFAULT FALSE NOT NULL, created varchar(255) NOT NULL, updated varchar(255) NOT NULL, status varchar(255) NOT NULL, uid INTEGER NOT NULL)";
            $this->connection->exec($query);

            $query = "CREATE TABLE IF NOT EXISTS `vocabularies` (vid INTEGER PRIMARY KEY AUTO_INCREMENT, vocabulary_name varchar(255), vocabulary_label varchar(255))";
            $this->connection->exec($query);

            $query = "CREATE TABLE IF NOT EXISTS `terms` (term_id INTEGER PRIMARY KEY AUTO_INCREMENT, term_name varchar(255) NOT NULL, vocabulary_id INTEGER NOT NULL)";
            $this->connection->exec($query);

            $query = "CREATE TABLE IF NOT EXISTS `term_nodes` (tnd INTEGER PRIMARY KEY AUTO_INCREMENT, tid INTEGER NOT NULL, nid INTEGER NOT NULL)";
            $this->connection->exec($query);

            $query = "CREATE TABLE IF NOT EXISTS `address_fields_data` (lid INT(11) PRIMARY KEY AUTO_INCREMENT, country_code varchar(20) NOT NULL, state_code varchar(20) NULL, city_id varchar(11) NULL, zip_code varchar(20), address_1 varchar(255), address_2 varchar(255), county varchar(255))";
            $this->connection->exec($query);
        }
    }

}