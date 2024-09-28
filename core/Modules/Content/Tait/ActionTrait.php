<?php

namespace Mini\Cms\Modules\Content\Tait;

use Mini\Cms\Connections\Database\Database;
use Mini\Cms\Modules\Content\Storage\FieldStorage;
use Symfony\Component\Yaml\Yaml;

trait ActionTrait
{
    protected array $FIELD;

    protected array $STORAGE;

    protected array $CONTENT_TYPE;

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
        if($this->FIELD['#field_type'] && $this->FIELD['#field_type'] === 'varchar') {
            $table = "CREATE TABLE node__field_".$this->FIELD['#field_name'];
            $storage_new = new FieldStorage($this->FIELD['#field_storage']);

            $table .= " (tid int(11) NULL, entity_id int(11) NOT NULL, field_".$this->FIELD['#field_name'].'_value varchar';
            $table .= "(".$storage_new->getSize().")";
            $table .= $storage_new->isNullable()  ? " NOT NULL" : " NULL";
            $table .= $storage_new->isNullable() ? " DEFAULT '".$storage_new->getDefault()."'" : null;
            $table .= ")";
            $st = Database::database()->prepare($table);
            $st->execute();
        }
    }
}