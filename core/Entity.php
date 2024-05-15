<?php

namespace Mini\Cms;


use Mini\Cms\Connections\Database\Database;
use Mini\Cms\StorageManager\Connector;
use Mini\Cms\StorageManager\FieldRequirementNotFulFilledException;

class Entity implements ConnectorInterface
{
    private array $entity_definitions;

    private Connector $connector;

    public function make(array $entity): int|null
    {
        $this->entity_definitions = [];
        if(array_key_exists('entity_type_name', $entity)) {
            $clean_name = preg_replace('/[^A-Za-z0-9]/', '_', $entity['entity_type_name']);
            $this->entity_definitions['entity_type_name'] = strtolower($clean_name);
            $this->entity_definitions['entity_label'] = $entity['entity_label'] ?? 'No Label';
            $this->entity_definitions['entity_type_description'] = $entity['entity_type_description'] ?? '';

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
        if(!isset($this->connector)) {
            $this->connector = new Connector();
        }

        $query = "SELECT * FROM entity_types WHERE entity_type_name = :entity_type_name";
        $statement = $this->connector->getConnection()->prepare($query);
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

        $statement = $this->connector->getConnection()->prepare($query);
        foreach ($fieldColumns as $fieldColumn) {
            $statement->bindParam(':' . $fieldColumn, $this->entity_definitions[$fieldColumn]);
        }

        // Making table presentation of field
        $statement->execute();
        return $this->connector->getConnection()->lastInsertId();
    }

    /**
     * Connector setter
     * @param Connector $connector Connector class object.
     * @return void
     */
    public function connector(Connector $connector): void
    {
        $this->connector = $connector;
    }

    /**
     * Finding entity
     * @param string $entity_name Entity machine name.
     * @return void
     */
    public function find(string $entity_name): void
    {
        $query = "SELECT * FROM entity_types WHERE entity_type_name = :entity_type_name";
        $statement = $this->connector->getConnection()->prepare($query);
        $statement->execute(['entity_type_name' => $entity_name]);
        $entity_types = $statement->fetchAll(\PDO::FETCH_ASSOC)[0] ?? [];
        $this->entity_definitions = $entity_types;
    }

    /**
     * Create New entity.
     * @param array $entity_definitions array of definitons
     * @param mixed|null $connector Connector class object
     * @return Entity
     * @throws EntityNotAvailableException
     */
    public static function create(array $entity_definitions, mixed $connector = null): Entity
    {
        $entity = new static();
        if($connector instanceof Connector) {
            $entity->connector = $connector;
        }
        $entity->make($entity_definitions);
        return $entity;
    }

    /**
     * @param string $entity_name Entity machine name
     * @param mixed|null $connector Connector class object.
     * @return Entity
     */
    public static function load(string $entity_name, mixed $connector = null): Entity
    {
        $entity = new static();
        if ($connector instanceof Connector) {
            $entity->connector = $connector;
        }else {
            $entity->connector = new Connector();
        }

        $entity->find($entity_name);
        return $entity;
    }

    /**
     * Entity id.
     * @return mixed|null
     */
    public function entityId()
    {
        return $this->entity_definitions['entity_type_id'] ?? null;
    }

    public static function entities(): array
    {
        $query = Database::database()->prepare("SELECT entity_type_name FROM entity_types");
        $query->execute();
        $entity_definitions = $query->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($entity_definitions as $key=>$entity_definition) {
            $entity_definitions[$key] = self::load($entity_definition['entity_type_name'], new Connector(external_connection: Database::database()));
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
}