<?php

namespace Mini\Cms\Modules\Modal;
use Mini\Cms\Connections\Database\Database;
use Mini\Cms\Modules\Modal\Columns\ColumnInterface;
use Mini\Cms\Services\Services;
use PDO;
use PSpell\Config;

/**
 * Modal works with tables in table.
 *
 */

abstract class Modal
{
    /**
     * Connection
     * @var Database
     */
    private Database $db;
    /**
     * @var string $main_table should be override in extender class with the main table name
     */
    protected string $main_table;

    /**
     * @var ColumnInterface $primary_key_column column name in the main table.
     */
    protected ColumnInterface $primary_key_column;

    /**
     * Columns of this main table defined by this modal.
     * @var array
     */
    protected array $columns;

    /**
     * This construct need to be called in extend classes.
     */
    public function __construct()
    {
        $this->db = new Database();

        // If the main table is not override with custom table will be using the class name.
        if(empty($this->main_table)) {
            $list = explode('\\', strtolower(get_class($this)));
            $this->main_table = end($list);
        }

        // table creation with default columns.
        if(!empty($this->main_table)) {
            $mandatory_columns = [
                "{$this->main_table}_uid int(11) NOT NULL",
                "{$this->main_table}_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP",
            ];
            $query = Database::database()->prepare("CREATE TABLE IF NOT EXISTS {$this->main_table} (".implode(', ', $mandatory_columns).")");
            $query->execute();
        }

        // Let's look for columns
        if(!empty($this->columns)) {

            foreach ($this->columns as $column) {
                if($column instanceof ColumnInterface) {
                    $column->create();
                }
            }
        }

        // Loading primary key column
        if(empty($this->primary_key_column) && !empty($this->columns)) {
            foreach ($this->columns as $column) {
                if($column instanceof ColumnInterface) {
                    if($column->isPrimary()) {
                        $this->primary_key_column = $column;
                    }
                }
            }
        }

        if(empty($this->primary_key_column)) {
            throw new PrimaryKeyColumnMissing("The primary key column is required.");
        }
    }

    /**
     * Loading all columns in table.
     * @param mixed $relation_table
     * @return array|false
     */
    private function identifyColumns(mixed $relation_table): array|false
    {

        $db = $this->db->getDatabaseName();
        $query = " SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = :db AND TABLE_NAME = :table";
        $query = $this->db->connect()->prepare($query);
        $query->bindParam(':table', $relation_table);
        $query->bindParam(':db', $db);
        $query->execute();
        return $query->fetchAll();
    }

    /**
     * Check if relation can be made.
     * @param false|array $columns
     * @return bool
     */
    private function hasRelationColumn(false|array $columns): bool
    {
        $column = array_filter($columns, fn ($column) => str_starts_with($column, $this->primary_key_column.'__'));
        return !empty($column);
    }

    /**
     * Get the main table name.
     * @return string
     */
    public function getMainTable(): string
    {
        return $this->main_table;
    }

    /**
     * Get all records of modal.
     * @return RecordCollections
     */
    public function all(): RecordCollections
    {
        return new RecordCollections($this->db->connect()?->query("SELECT * FROM {$this->main_table} ORDER BY {$this->main_table}_created DESC")->fetchAll() ?? []);
    }

    /**
     * Get record of modal.
     * @param string|int $value
     * @param string|null $field_name
     * @return RecordCollections
     */
    public function get(string|int $value, string|null $field_name = null): RecordCollections
    {
        if($field_name) {
            foreach ($this->columns as $column) {
                if($column instanceof ColumnInterface) {
                    if($column->getName() === $field_name) {
                        $this->primary_key_column = $column;
                    }
                }
            }
        }
        $query = $this->db->connect()?->prepare("SELECT * FROM {$this->main_table} WHERE {$this->primary_key_column->getName()} = :{$this->primary_key_column->getName()}");
        $query->bindParam(':'.$this->primary_key_column->getName(), $value);
        $query->execute();
        return new RecordCollections($query->fetchAll());
    }

    /**
     * Saving data of modal to storage.
     * @param array $values
     * @return RecordCollections|bool
     * @throws MissingDefaultValueForUnNullableColumn
     */
    public function store(array $values): RecordCollections|bool
    {
       $processed = [
           $this->main_table.'_uid' => Services::create('current.user')->id(),
       ];
       if(!empty($this->columns)) {
           foreach($this->columns as $column) {
               if($column instanceof ColumnInterface) {
                   if(!empty($values[$column->getName()])) {
                       $processed[$column->getName()] = $values[$column->getName()];
                   }
                   elseif ($column->isNullable())
                   {
                       $processed[$column->getName()] = null;
                   }
                   elseif(!$column->isNullable() && !$column->isDefined() && !$column->isAutoIncrement()) {
                       throw new MissingDefaultValueForUnNullableColumn("Column '{$column->getName()}' is not nullable");
                   }
               }
           }
       }
       if(!empty($processed)) {
           $columns = array_keys($processed);
           $placeholders = array_map(function($column) {return ":$column"; },$columns);
           $query_line = "INSERT INTO {$this->main_table} (" . implode(', ', $columns).") VALUES (".implode(', ', $placeholders).")";
           $query = $this->db->connect()->prepare($query_line);
           foreach ($columns as $column) {
               $query->bindParam(':'.$column, $processed[$column]);
           }
           $query->execute();
           return $this->get($this->db->connect()->lastInsertId());
       }

       return false;
    }

    /**
     * Deleting record from modal records.
     * @param string|int $value
     * @return bool
     */
    public function delete(string|int $value): bool
    {
        if(!empty($this->primary_key_column)) {
            $query = "DELETE FROM {$this->main_table} WHERE {$this->primary_key_column->getName()} = :value";
            $query = $this->db->connect()->prepare($query);
            $query->bindParam(':value', $value);
            return $query->execute();
        }
        return false;
    }

    /**
     * Update record.
     * @param array $values
     * @param string|int $key
     * @return false|RecordCollections
     */
    public function update(array $values, string|int $key): false|RecordCollections
    {
        if(!empty($this->columns)) {
            foreach($this->columns as $column) {
                if($column instanceof ColumnInterface) {
                    if(!empty($values[$column->getName()])) {
                        $processed[$column->getName()] = $values[$column->getName()];
                    }
                }
            }
        }
        if(!empty($processed)) {
            $columns = array_keys($processed);
            $placeholders = array_map(function($column) {return "$column = :$column"; },$columns);
            $query_line = "UPDATE {$this->main_table} SET " . implode(', ', $placeholders)." WHERE {$this->primary_key_column->getName()} = :value";
            $query = $this->db->connect()->prepare($query_line);
            foreach ($columns as $column) {
                $query->bindParam(':'.$column, $processed[$column]);
            }
            $query->bindParam(':value', $key);
            $query->execute();
            return $this->get($key);
        }

        return false;
    }

    /**
     * Building column instance
     * @param string $column Complete class name.
     * @return ColumnInterface
     * @throws ColumnClassNotFound
     */
    public  static function buildColumnInstance(string $column): ColumnInterface
    {
        if(class_exists($column)) {
            $column = new $column();
            if($column instanceof ColumnInterface) {
                return $column;
            }
        }
        throw new ColumnClassNotFound("Column '{$column}' not found");
    }

    /**
     * CAUSATION: This will drop modal storage.
     * @return bool
     */
    public function destroy(): bool
    {
        return $this->db->connect()->query("DROP TABLE {$this->main_table}")->execute();
    }

    /**
     * CAUSATION: You will be emptying all data in modal.
     * @return bool
     */
    public function truncate(): bool
    {
        return $this->db->connect()->query("TRUNCATE TABLE {$this->main_table}")->execute();
    }

    /**
     * Saving from an array of arrays.
     * @param array $values
     * @return array|bool
     * @throws MissingDefaultValueForUnNullableColumn
     */
    public function massStore(array $values): array|bool
    {
        if(empty($values)) {
            return false;
        }
        $processed = [];
        foreach($values as $value) {
            $processed[] = $this->store($value);
        }
        return $processed;
    }

    /**
     * Getting collection of given range
     * @param int $limit
     * @param int $offset
     * @return RecordCollections
     */
    public function range(int $limit, int $offset): RecordCollections
    {
       return new RecordCollections($this->db->connect()->query("SELECT * FROM $this->main_table ORDER BY {$this->main_table}_created DESC limit $limit OFFSET $offset")->fetchAll() ?? []);
    }

    /**
     * Get records belongs to uid.
     * @param int $uid
     * @return RecordCollections
     */
    public function byOwner(int $uid): RecordCollections
    {
        $query = $this->db->connect()?->prepare("SELECT * FROM {$this->main_table} WHERE {$this->main_table}_uid = :uid");
        $query->bindParam(':uid', $uid);
        $query->execute();
        return new RecordCollections($query->fetchAll());
    }
}