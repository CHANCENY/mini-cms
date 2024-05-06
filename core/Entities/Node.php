<?php

namespace Mini\Cms\Entities;

use Cassandra\Date;
use Chance\Entity\NodeInterface;
use Chance\Entity\StorageManager\Connector;
use PDO;

class Node implements NodeInterface
{
    private array $data;

    private array $fields;

    private Connector $connector;

    public function __construct()
    {
        $this->data = [];
    }

    public function id()
    {
        return $this->data['#node']['node_id']['value'];
    }

    public function type()
    {
        return $this->data['#node']['bundle']['value'];
    }

    public function getValues()
    {
        return $this->data['#values'];
    }

    public function set(string $key, mixed $value): void
    {
        if($key === 'title') {
            $this->data['#node']['title']['value'] = $value;
        }
        else {
            if(gettype($value) == 'array') {
                foreach ($value as $item=>$d) {
                    $this->data['#values'][$key]['values'][] = $d;
                }
            }
            else {
                try {
                    if(!empty($this->getValue($key))) {
                        $this->data['#values'][$key][0]['value'] = $value;
                    }else {
                        $this->data['#values'][$key]['value'] = $value;
                    }
                }catch (\Chance\Entity\Entities\FieldNotFoundException $exception) {
                    $this->data['#values'][$key][]['value'] = $value;
                }
            }
        }
    }

    public function get(string $key)
    {
        return $this->data['#values'][$key] ?? throw new FieldNotFoundException('Field not found');
    }

    /**
     * @param string $entity_name
     * @return Node|$this|null
     */
    public function make(string $entity_name): Node|null
    {
        $query = "SELECT * FROM entity_types WHERE entity_type_name = :entity_name";
        $statement = $this->connector->getConnection()->prepare($query);
        $statement->bindValue(':entity_name', $entity_name);
        $statement->execute();
        $result = $statement->fetchAll(\PDO::FETCH_ASSOC)[0] ?? [];
        if(!empty($result['entity_type_name'])){
            $this->data['#node']['bundle']['value'] = $entity_name;
            $this->data['#node']['status']['value'] = 'Yes';
            $this->data['#node']['created']['value'] = (new \DateTime('now'))->getTimestamp();
            $this->data['#node']['updated']['value'] = (new \DateTime('now'))->getTimestamp();
            $this->data['#node']['deleted']['value'] = 0;
            $this->data['#node']['uid']['value'] = 1;

            $query = "SELECT * FROM entity_types_fields WHERE entity_type_id = :id";
            $statement = $this->connector->getConnection()->prepare($query);
            $statement->bindValue(':id', $result['entity_type_id']);
            $statement->execute();
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC) ?? [];
            foreach ($result as $field=>$value) {
                $this->fields['#fields'][] = [
                    '#id' => $value['entity_type_field_id'],
                    '#name' => $value['field_name'],
                    '#type' => $value['field_type'],
                    '#description' => $value['field_description'],
                    '#label' => $value['field_label'],
                    '#settings' => json_decode($value['field_settings'],true),
                ];
            }

        }else {
            return null;
        }
        return $this;
    }

    /**
     * @param int $node_id
     * @return $this|null
     */
    public function find(int $node_id)
    {
        $query = "SELECT * FROM entity_node_data WHERE node_id = :id";
        $statement = $this->connector->getConnection()->prepare($query);
        $statement->bindValue(':id', $node_id);
        $statement->execute();
        $result = $statement->fetchAll(\PDO::FETCH_ASSOC)[0] ?? [];

        $node_id = null;
        $entity_type = null;
        if(!empty($result)) {
            $node_id = $result['node_id'] ?? null;
            $entity_type = $result['bundle'] ?? null;
            foreach ($result as $field=>$value) {
                $this->data['#node'][$field]['value'] = $value;
                $this->data['#values'][$field][0] = [
                    'value' => $value,
                   // 'id' => $node_id,
                ];
            }
        }

        if(!empty($node_id) && !empty($entity_type)) {

            $query = "SELECT * FROM entity_types WHERE entity_type_name = :entity_name";
            $statement = $this->connector->getConnection()->prepare($query);
            $statement->bindValue(':entity_name', $entity_type);
            $statement->execute();
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC)[0] ?? [];

            if(!empty($result)) {
                $query = "SELECT * FROM entity_types_fields WHERE entity_type_id = :id";
                $statement = $this->connector->getConnection()->prepare($query);
                $statement->bindValue(':id', $result['entity_type_id']);
                $statement->execute();
                $result = $statement->fetchAll(\PDO::FETCH_ASSOC) ?? [];
                foreach ($result as $field=>$value) {
                    $this->fields['#fields'][] = [
                        '#id' => $value['entity_type_field_id'],
                        '#name' => $value['field_name'],
                        '#type' => $value['field_type'],
                        '#description' => $value['field_description'],
                        '#label' => $value['field_label'],
                        '#settings' => json_decode($value['field_settings'],true),
                    ];
                }
            }
        }

        if(!empty($this->fields)) {
            $tables = array_map(function ($field){
                return 'field__'.$field['#name'];
            }, $this->fields['#fields']);

            if($tables) {
                foreach ($tables as $table) {
                    $valueField = $table . '__value';
                    $query = "SELECT $valueField, field_id FROM $table WHERE entity_id = :id";
                    $statement = $this->connector->getConnection()->prepare($query);
                    $statement->bindValue(':id', $node_id);
                    $statement->execute();
                    $result = $statement->fetchAll(\PDO::FETCH_ASSOC) ?? [];
                    foreach ($result as $value) {
                        $this->data['#values'][$table][] = [
                            'value' => $value[$valueField],
                           // 'id' => $value['field_id'],
                        ];
                    }
                }
            }
        }

        if(empty($this->data) || empty($this->fields)) {
            return null;
        }

        return $this;
    }

    /**
     * @param Connector $connector
     * @return void
     */
    public function connector(Connector $connector): void
    {
        $this->connector = $connector;
    }

    /**
     * @return mixed Title of node.
     */
    public function getTitle(): mixed
    {
        return $this->data['#node']['title']['value'];
    }

    public function setTitle(string $title)
    {
        // TODO: Implement setTitle() method.
    }

    /**
     * Saving new node.
     * @return false|string
     * @throws DefaultNodeFieldNotProvidedException
     * @throws FieldDefaultValueNotFoundForRequiredException
     * @throws FieldMaxSizeException
     * @throws NodeTitleNotProvidedException
     */
    public function save()
    {
        // Validate node defaults fields
        foreach ($this->data['#node'] as $field=>$value) {
            if($field === 'title' && empty($value['value'])) {
                throw new NodeTitleNotProvidedException('title cannot be empty');
            }
            if(empty($value['value']) && $field !== 'deleted') {
                throw new DefaultNodeFieldNotProvidedException($field . ' cannot be empty');
            }
        }

        // Validate fields data based on given field settings
        foreach ($this->fields['#fields'] as $field=>$value) {

           $field_data = $this->data['#values'];
            $settings = $value['#settings'];

            if(!empty($field_data) && isset($value['#name'])) {
               $field_name = "field__" .$value['#name'];
               $settableValue = $field_data[$field_name]['value'] ?? $field_data[$field_name]['values'] ?? null;

               $validateSettings = function ($settableValue, $destination = 'value', $index = null) use ($field_data, $value, $field_name, $settings) {

                   // Required checking.
                   if(!empty($settings['field_required']) && $settings['field_required'] === 'NOT NULL') {

                       if(empty($settableValue)) {
                           // find default value.
                           $default_value = $settings['field_default_value'] ?? null;
                           if(empty($default_value)) {
                               throw new FieldDefaultValueNotFoundForRequiredException($field_name . ' cannot be empty');
                           }
                           if($destination === 'values') {
                               $this->data['#values'][$field_name][$destination][$index] = $default_value;
                           }else {
                               $this->data['#values'][$field_name][$destination] = $default_value;
                           }
                       }
                   }
                   else {
                       if($destination === 'values') {
                           $this->data['#values'][$field_name][$destination][$index] = $settableValue;
                       }else {
                           $this->data['#values'][$field_name][$destination] = $settableValue;
                       }
                   }

                   // Size checking.
                   if(!empty($settings['field_size'])) {

                       if(gettype($settableValue) === 'string' && $settings['field_size'] < strlen($settableValue)) {
                           throw new FieldMaxSizeException($field_name . ' value cannot be greater than ' . $settings['field_size']);
                       }
                   }
               };

               // Now we have value set and field settings
               if(gettype($settableValue) == 'array') {
                   foreach ($settableValue as $key=>$settableValueItem) {
                      $validateSettings($settableValueItem, 'values',$key);
                   }
               }
               else {
                   $validateSettings($settableValue, 'value');
               }
           }
        }

        // Now we can start saving node data
        $node_fields = array_keys($this->data['#node']);
        $node_fields_line = implode(', ', $node_fields);
        $placeholders = null;
        foreach ($node_fields as $node_field) {
            $placeholders .= ":$node_field, ";
        }
        $placeholders = rtrim($placeholders, ', ');
        $node_fields_line = rtrim($node_fields_line, ', ');
        $query = "INSERT INTO entity_node_data ($node_fields_line) VALUES ($placeholders)";

        // Insert in entity_node_data
        $statement = $this->connector->getConnection()->prepare($query);
        foreach ($node_fields as $node_field) {
            $statement->bindParam(':'.$node_field, $this->data['#node'][$node_field]['value']);
        }
        $statement->execute();
        $node_id = $this->connector->getConnection()->lastInsertId();

        // Now since we have saved node lets add or other fields
        $tables_notifier = function ($field) {
            try {
                $query = "SELECT * FROM $field LIMIT 1";
                $statement = $this->connector->getConnection()->prepare($query);
                $statement->execute();
                return true;
            }catch (\PDOException $e){
                return false;
            }
        };

        if($node_id) {
            $inserter = function ($table, $value) use ($node_id) {
                $fields = [
                    $table . '__value' => $value,
                    'entity_id' => $node_id
                ];
                $fields_line = implode(', ', array_keys($fields));
                $placeholders = null;
                foreach (array_keys($fields) as $field) {
                    $placeholders .= ":$field, ";
                }
                $placeholders = rtrim($placeholders, ', ');
                $fields_line = rtrim($fields_line, ', ');
                $query = "INSERT INTO $table ($fields_line) VALUES ($placeholders)";

                $statement = $this->connector->getConnection()->prepare($query);
                foreach ($fields as $field => $value) {
                    $statement->bindParam(':'.$field, $fields[$field]);
                }
                return $statement->execute();
            };

            // Inserting fields data.
            foreach ($this->data['#values'] as $field=>$value) {
                if($tables_notifier($field)) {

                    // Working on found table

                    // Inserting values for values key
                    if(!empty($value['values']) && gettype($value['values']) === 'array') {
                        foreach ($value['values'] as $valueItem) {
                           $inserter($field, $valueItem);
                        }
                    }

                    // Inserting values for value key
                    if(!empty($value['value'])) {
                       $inserter($field, $value['value']);
                    }

                }
            }
        }

        return $node_id;
    }

    /**
     * Deleting node.
     * @return bool True is deleted.
     */
    public function delete()
    {
        $query = "UPDATE entity_node_data SET deleted = 1 WHERE node_id = :id";
        $statement = $this->connector->getConnection()->prepare($query);
        $statement->bindValue(':id',$this->id());
        return $statement->execute();
    }

    /**
     * Checking if node is deleted.
     * @return bool
     * @throws FieldNotFoundException
     */
    public function isDeleted(): bool
    {
        return !empty($this->getValue('deleted'));
    }

    /**
     * Updating node.
     * @return bool
     */
    public function update(): bool
    {
        // Simple Lets target fields
        $data = $this->getValues();
        $node_id = $this->id();

        $updater = function ($field, $value) use ($node_id) {
            $query = "SELECT * FROM $field WHERE entity_id = :node_id LIMIT 1";
            $statement = $this->connector->getConnection()->prepare($query);
            $statement->bindValue(':node_id',$node_id);
            $statement->execute();
            $data = $statement->fetch(PDO::FETCH_ASSOC);
            $fieldValue = $field . '__value';
            if(empty($data)) {
                $query = "INSERT INTO $field ('entity_id', $fieldValue) VALUES (:node_id, :field)";
                $statement = $this->connector->getConnection()->prepare($query);
                $statement->bindValue(':node_id',$node_id);
                $statement->bindValue(':field',$value);
                return $statement->execute();
            }else {
                $query = "UPDATE $field SET $fieldValue = :field WHERE entity_id = :node_id";
                $statement = $this->connector->getConnection()->prepare($query);
                $statement->bindValue(':field', $value);
                $statement->bindValue(':node_id', $node_id);
                return $statement->execute();
            }
        };

        foreach ($data as $field=>$value) {
            if(str_starts_with($field,'field__field')) {
                if(count($value) === 1) {
                    $updater($field, $value[0]['value']);
                }else {
                    foreach ($value as $key=>$valueItem) {
                        $updater($field, $valueItem['value']);
                    }
                }
            }
            else {
                $query = "UPDATE entity_node_data SET $field = :field WHERE node_id = :node_id";
                if($field !== 'node_id' && $field !== 'updated' && $field !== 'created') {
                    $statement = $this->connector->getConnection()->prepare($query);
                    $statement->bindValue(':field', $value[0]['value']);
                    $statement->bindValue(':node_id', $node_id);
                    $statement->execute();
                }
            }
        }
        // Let's change updated
        $query = "UPDATE entity_node_data SET updated = :uo WHERE node_id = :id";
        $statement = $this->connector->getConnection()->prepare($query);
        $updated = (new \DateTime('now'))->getTimestamp();
        $statement->bindValue(':uo', $updated);
        $statement->bindValue(':id', $node_id);
        return $statement->execute();
    }

    /**
     * Checking if node was updated.
     * @return bool True if was updated.
     * @throws FieldNotFoundException
     */
    public function isUpdated(): bool
    {
        return (int) $this->getValue('updated') > (int) $this->getValue('created');
    }

    /**
     * @param string $entity_name Entity name
     * @param mixed|null $connector Database connection.
     * @return Node|null
     * Node is return
     */
    public static function create(string $entity_name, mixed $connector = null): Node|null
    {
        $entity = new Node();
        $entity->connector($connector);
        return $entity->make($entity_name);
    }

    /**
     * Load node.
     * @param int $node_id Node id.
     * @param mixed|null $connector Database connection.
     * @return Node|null
     * Returns Node if found all null.
     */
    public static function load(int $node_id, mixed $connector = null): Node|null
    {
        $entity = new Node();
        $entity->connector($connector);
        return $entity->find($node_id);
    }

    /**
     * Getting value of field.
     * @param string $field Field machine name.
     * @return mixed Array if multiple values found.
     * @throws FieldNotFoundException
     */
    public function getValue(string $field): mixed
    {
        $value = $this->get($field);
        if(count($value) === 1) {
            return $value[0]['value'] ?? null;
        }
        return $value;
    }

    /**
     * Un deleting the node.
     * @return bool True if node is un deleted success.
     */
    public function unDelete(): bool
    {
        $query = "UPDATE entity_node_data SET deleted = 0 WHERE node_id = :id";
        $statement = $this->connector->getConnection()->prepare($query);
        $statement->bindValue(':id',$this->id());
        return $statement->execute();
    }

}