<?php

namespace Mini\Cms;


use Mini\Cms\Connections\Database\Database;
use Mini\Cms\Fields\FileField;
use Mini\Cms\Fields\ReferenceField;
use Mini\Cms\Fields\TextAreaField;
use Mini\Cms\Fields\TextField;
use Mini\Cms\StorageManager\FieldRequirementNotFulFilledException;
use Throwable;

class Entity
{
    private array $entity_definitions;

    private array|false $entity_fields;

    public function getEntityFields(): false|array
    {
        return $this->entity_fields;
    }

    public function make(array $entity): Entity|null
    {
        $this->entity_definitions = [];
        if(array_key_exists('entity_type_name', $entity)) {
            $clean_name = preg_replace('/[^A-Za-z0-9]/', '_', $entity['entity_type_name']);
            $this->entity_definitions['entity_type_name'] = strtolower($clean_name);
            $this->entity_definitions['entity_label'] = $entity['entity_label'] ?? 'No Label';
            $this->entity_definitions['entity_type_description'] = $entity['entity_type_description'] ?? '';
            return $this;
        }else {
            throw new EntityNotAvailableException();
        }
        return null;
    }

    /**
     * Save entity definitions.
     * @return int|null
     * @throws FieldRequirementNotFulFilledException
     */
    public  function save(): int|null
    {
        $query = "SELECT * FROM entity_types WHERE entity_type_name = :entity_type_name";
        $statement = Database::database()->prepare($query);
        $statement->execute(['entity_type_name' => $this->entity_definitions['entity_type_name']]);
        $entity_definitions = $statement->fetchAll(\PDO::FETCH_ASSOC);

        if(!empty($entity_definitions)) {
            return $entity_definitions[0]['entity_type_id'] ?? null;
        }

        $fieldColumns = array_keys($this->entity_definitions);

        $placeholders = '(';
        foreach ($fieldColumns as $fieldColumn) {
            if(empty($this->entity_definitions[$fieldColumn])) {
                throw new FieldRequirementNotFulFilledException($fieldColumn);
            }
            if(gettype($this->entity_definitions[$fieldColumn]) == 'array') {
                $this->entity_definitions[$fieldColumn] = json_encode($this->entity_definitions[$fieldColumn],JSON_PRETTY_PRINT);
            }
            $placeholders .= ':' . $fieldColumn . ', ';
        }
        $placeholders = trim($placeholders, ', ');
        $placeholders .= ')';

        $query = "INSERT INTO entity_types (".implode(',', $fieldColumns).") VALUES ".$placeholders;

        $statement = Database::database()->prepare($query);
        foreach ($fieldColumns as $fieldColumn) {
            $statement->bindParam(':' . $fieldColumn, $this->entity_definitions[$fieldColumn]);
        }

        // Making table presentation of field
        $statement->execute();
        return Database::database()->lastInsertId();
    }

    /**
     * Finding entity
     * @param string $entity_name Entity machine name.
     * @return Entity|null
     */
    public function find(string $entity_name): Entity|null
    {
        $query = "SELECT * FROM entity_types WHERE entity_type_name = :entity_type_name";
        $statement = Database::database()->prepare($query);
        $statement->execute(['entity_type_name' => $entity_name]);
        $entity_types = $statement->fetchAll(\PDO::FETCH_ASSOC)[0] ?? [];
        $this->entity_definitions = $entity_types;

        $entity_id = $this->entityId();
        $query = Database::database()->prepare("SELECT * FROM entity_types_fields WHERE entity_type_id = :id");
        $query->execute(['id' => $entity_id]);
        $this->entity_fields = $query->fetchAll(\PDO::FETCH_ASSOC);
        if($this->entity_fields) {
            foreach ($this->entity_fields as $k=>$field) {
                $this->entity_fields[$k] = Field::load($field['field_name']);
            }
        }
        return $this;
    }

    /**
     * Create New entity.
     * @param array $entity_definitions array of definitions
     * @param mixed|null $connector Connector class object
     * @return Entity
     * @throws EntityNotAvailableException
     */
    public static function create(array $entity_definitions, mixed $connector = null): Entity
    {
        return (new Entity())->make($entity_definitions);
    }

    /**
     * @param string $entity_name Entity machine name
     * @return Entity
     */
    public static function load(string $entity_name): Entity
    {
        return (new Entity())->find($entity_name);
    }

    /**
     * Entity id.
     * @return mixed|null
     */
    public function entityId(): mixed
    {
        return $this->entity_definitions['entity_type_id'] ?? null;
    }

    public static function entities(): array
    {
        $query = Database::database()->prepare("SELECT entity_type_name FROM entity_types");
        $query->execute();
        $entity_definitions = $query->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($entity_definitions as $key=>$entity_definition) {
            $entity_definitions[$key] = self::load($entity_definition['entity_type_name']);
        }
        return $entity_definitions;
    }

    public function getEntityTypeName(): string
    {
        return $this->entity_definitions['entity_type_name'] ?? '';
    }

    public function getEntityLabel(): string
    {
        return $this->entity_definitions['entity_label'] ?? '';
    }

    public function getEntityTypeDescription(): string
    {
        return $this->entity_definitions['entity_type_description'] ?? '';
    }

    public function setEntityTypeName(string $entity_type_name): void
    {
        $this->entity_definitions['entity_type_name'] = $entity_type_name;
    }

    public function setEntityLabel(string $entity_label): void
    {
        $this->entity_definitions['entity_label'] = $entity_label;
    }

    public function setEntityTypeDescription(string $entity_type_description): void
    {
        $this->entity_definitions['entity_type_description'] = $entity_type_description;
    }

    /**
     * Update entity.
     * @return bool
     */
    public function update(): bool
    {
        $query = Database::database()->prepare("UPDATE entity_types SET entity_label = :entity_label, entity_type_description = :entity_type_description WHERE entity_type_name = :entity_type_name");
        return $query->execute([
            'entity_label' => $this->entity_definitions['entity_label'],
            'entity_type_name' => $this->entity_definitions['entity_type_name'],
            'entity_type_description' => $this->entity_definitions['entity_type_description']
        ]);
    }

    public function delete(): bool
    {
        // Load all fields attached to this entity.
        $entity_id = $this->entityId();
        $query = Database::database()->prepare("SELECT entity_type_field_id, field_name FROM entity_types_fields WHERE entity_type_id = :id");
        $query->execute(['id' => $entity_id]);
        $data = $query->fetchAll(\PDO::FETCH_ASSOC);
        $flag = false;
        if($data) {
            foreach ($data as $key=>$value) {
                try {
                    $table = "field__".$value['field_name'];
                    $field_id = $value['entity_type_field_id'];
                    $query = Database::database()->prepare("DROP TABLE $table");
                    $query->execute();
                    $query = Database::database()->prepare("DELETE FROM entity_types_fields WHERE entity_type_field_id = :id");
                    $query->execute(['id' => $field_id]);
                    $flag = true;
                }catch (Throwable $exception) {
                    return false;
                }
            }
        }
        else {
            $flag = true;
        }
        if($flag) {
            $bundle = $this->getEntityTypeName();
            $query = Database::database()->prepare("DELETE FROM entity_node_data WHERE bundle = :bundle");
            $query->execute(['bundle' => $bundle]);


            $query = Database::database()->prepare("DELETE FROM entity_types WHERE entity_type_name = :entity");
            $query->execute(['entity' => $bundle]);
            return true;
        }
        return false;
    }
}