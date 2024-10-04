<?php

namespace Mini\Cms\Modules\Content\Tait;

use Mini\Cms\Connections\Database\Database;
use Mini\Cms\Connections\Database\Queries\QueryManager;
use Mini\Cms\Modules\Content\Storage\FieldStorage;
use Symfony\Component\Yaml\Yaml;

trait ActionTrait
{
    protected array $FIELD;

    protected array $STORAGE;

    protected array $CONTENT_TYPE;

    protected array $field_data;

    protected function write(string $location, mixed $data): bool
    {
        $data = Yaml::dump($data);
        if(file_exists($location)) {
            throw new ConfigFileAlreadyExistException("configuration $location already exist");
        }
        return !empty(file_put_contents($location, $data));
    }

    protected function read(string $location): array|null
    {
        if(!file_exists($location)) {
            return null;
        }
        return Yaml::parseFile($location);
    }

    protected function overwrite(string $location, mixed $data): bool
    {
       $data = Yaml::dump($data);
        return !empty(file_put_contents($location, $data));
    }

    protected function remove(string $location): bool
    {
        if(file_exists($location)) {
            return unlink($location);
        }
        return false;
    }

    protected function prepare()
    {
        @mkdir("private://configs/storages");
        @mkdir("private://configs/types");
        @mkdir("private://configs/fields");
        @file_put_contents("private://configs/.htaccess", 'Deny from all');
    }

    protected function fieldTableCreation(): void
    {
        if($this->FIELD['#field_type'] && $this->FIELD['#field_type'] === 'text') {
            $table = "CREATE TABLE node__field_".$this->FIELD['#field_name'];
            $storage_new = new FieldStorage($this->FIELD['#field_storage']);

            $table .= " (field_id int(11) primary key auto_increment ,entity_id int(11) NOT NULL, field_".$this->FIELD['#field_name'].'_value varchar';
            $table .= "(".$storage_new->getSize().")";
            $table .= $storage_new->isNullable()  ? " NOT NULL" : " NULL";
            $table .= $storage_new->isNullable() ? " DEFAULT '".$storage_new->getDefault()."'" : null;
            $table .= ")";
            $st = Database::database()->prepare($table);
            $st->execute();
        }
        if($this->FIELD['#field_type'] && $this->FIELD['#field_type'] === 'file') {
            $table = "CREATE TABLE node__field_".$this->FIELD['#field_name'];
            $storage_new = new FieldStorage($this->FIELD['#field_storage']);

            $table .= " (field_id int(11) primary key auto_increment ,entity_id int(11) NOT NULL, field_".$this->FIELD['#field_name'].'_value int';
            $table .= "(".$storage_new->getSize().")";
            $table .= $storage_new->isNullable()  ? " NOT NULL" : " NULL";
            $table .= $storage_new->isNullable() ? " DEFAULT '".$storage_new->getDefault()."'" : null;
            $table .= ")";
            $st = Database::database()->prepare($table);
            $st->execute();
        }
    }
    
    protected function fieldTableDelete(): bool
    {
        $query = "DROP TABLE node__field_{$this->FIELD['#field_name']}";
        $st = Database::database()->prepare($query);
        return $st->execute();
    }

    protected function fieldData(int $nid): array
    {
        $query = new QueryManager(Database::database());
        $query->select("node__field_{$this->FIELD['#field_name']}", "f");
        $query->selectFields(["field_{$this->FIELD['#field_name']}_value AS {$this->FIELD['#field_name']}, field_id"]);
        $query->addCondition("entity_id", $nid);
        $st = $query->execute();
        $data = $st->fetchAll(\PDO::FETCH_ASSOC);
        $this->field_data = $data;
        return array_map(function ($row) {
            return $row[$this->FIELD['#field_name']];
        },$data);
    }

    public function createFieldData(int $nid, mixed $data): void
    {
        $query = new QueryManager(Database::database());
        if(is_array($data)) {
            foreach($data as $key => $value) {
                $query->insert("node__field_{$this->FIELD['#field_name']}");
                $query->addField("field_{$this->FIELD['#field_name']}_value", $value);
                $query->addField("entity_id", $nid);
                $query->execute();
            }
        }
        else {
            $query->insert("node__field_{$this->FIELD['#field_name']}");
            $query->addField("field_{$this->FIELD['#field_name']}_value", $data);
            $query->addField("entity_id", $nid);
            $query->execute();
        }
    }

    protected function getFieldDataId(string $value): ?int
    {
        if(empty($this->field_data)) {
            return null;
        }
        $ids = array_filter($this->field_data,function($item) use ($value) {
            if($item[$this->FIELD['#field_name']] == $value) {
                return $item['field_id'];
            }
        });
        if(empty($ids)) {
            return null;
        }
        return reset($ids);
    }

    protected function updateFieldAction(int $field_id, mixed $value): bool
    {
        $query = new QueryManager(Database::database());
        $query->update("node__field_{$this->FIELD['#field_name']}");
        $query->addField("field_{$this->FIELD['#field_name']}_value", $value);
        $query->addCondition("field_id", $field_id);
        return $query->execute()->rowCount();
    }
}