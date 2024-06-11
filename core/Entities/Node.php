<?php

namespace Mini\Cms\Entities;

use Mini\Cms\Connections\Database\Database;
use Mini\Cms\Field;
use Mini\Cms\Fields\FieldInterface;
use Mini\Cms\Modules\CurrentUser\CurrentUser;
use Mini\Cms\Modules\Extensions\Extensions;
use Mini\Cms\NodeInterface;
use PDO;

class Node implements NodeInterface
{
    private array $data;

    private array $fields;

    public function getFields(): array
    {
        return $this->fields['#fields'] ?? [];
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function __construct()
    {
        $this->data = [];
        $this->fields['#fields'] = [];
    }

    public function id()
    {
        return $this->data['#node']['node_id']['value'];
    }

    public function type()
    {
        return $this->data['#node']['bundle']['value'];
    }

    public function updatedOn(string $format)
    {
        return date($format,(int)$this->data['#node']['updated']['value']);
    }

    public function createdOn(string $format)
    {
        return date($format,(int)$this->data['#node']['created']['value']);
    }

    public function author(): User
    {
        return User::load((int) $this->data['#node']['uid']['value']);
    }

    public function getValues()
    {
        return $this->data['#values'];
    }

    public function set(string $key, mixed $value): void
    {
        // Let's loop through all fields of this entity to check if $key is actual field.
       if($this->fields['#fields']) {
           foreach ($this->fields['#fields'] as $field) {
               if($field instanceof FieldInterface && $key === $field->getName()) {
                   $field->setData($value);
               }
           }
       }

       $default_fields = array_keys($this->data['#node']);
       if(in_array($key, $default_fields)) {
           $this->data['#node'][$key]['value'] = $value;
       }
    }

    public function get(string $key)
    {
        return $this->data['#values'][$key] ?? null;
    }

    /**
     * @param string $entity_name
     * @return Node|$this|null
     */
    public function make(string $entity_name): Node|null
    {
        $query = "SELECT * FROM entity_types WHERE entity_type_name = :entity_name";
        $statement = Database::database()->prepare($query);
        $statement->bindValue(':entity_name', $entity_name);
        $statement->execute();
        $result = $statement->fetchAll(\PDO::FETCH_ASSOC)[0] ?? [];
        if(!empty($result['entity_type_name'])){
            $this->data['#node']['bundle']['value'] = $entity_name;
            $this->data['#node']['status']['value'] = 'Yes';
            $this->data['#node']['created']['value'] = (new \DateTime('now'))->getTimestamp();
            $this->data['#node']['updated']['value'] = (new \DateTime('now'))->getTimestamp();
            $this->data['#node']['deleted']['value'] = 0;
            $this->data['#node']['uid']['value'] = (new CurrentUser())->id();
            $this->data['#node']['title']['value'] = null;

            $query = "SELECT * FROM entity_types_fields WHERE entity_type_id = :id";
            $statement = Database::database()->prepare($query);
            $statement->bindValue(':id', $result['entity_type_id']);
            $statement->execute();
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC) ?? [];
            foreach ($result as $field=>$value) {
                $this->fields['#fields'][] = Field::load($value['field_name']);
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
        $statement = Database::database()->prepare($query);
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
            $statement = Database::database()->prepare($query);
            $statement->bindValue(':entity_name', $entity_type);
            $statement->execute();
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC)[0] ?? [];

            if(!empty($result)) {
                $query = "SELECT * FROM entity_types_fields WHERE entity_type_id = :id";
                $statement = Database::database()->prepare($query);
                $statement->bindValue(':id', $result['entity_type_id']);
                $statement->execute();
                $result = $statement->fetchAll(\PDO::FETCH_ASSOC) ?? [];
                foreach ($result as $field=>$value) {
                    $this->fields['#fields'][] = Field::load($value['field_name']);
                }
            }
        }

        if(!empty($this->fields['#fields'])) {
            foreach ($this->fields['#fields'] as $field=>$value) {
                $value = is_array($value) ? reset($value) : $value;
                if($value instanceof FieldInterface) {
                    $this->data['#values'][$value->getName()][] = [
                        'value' => $value->fetchData($node_id),
                    ];
                }
            }
        }

        if(empty($this->data) || empty($this->fields)) {
            return null;
        }

        return $this;
    }

    /**
     * @return mixed Title of node.
     */
    public function getTitle(): mixed
    {
        return $this->data['#node']['title']['value'];
    }
    

    public function published(): bool
    {
        return $this->data['#node']['status']['value'] === 'Yes';
    }

    public function setTitle(string $title)
    {
        // TODO: Implement setTitle() method.
        $this->data['#node']['title']['value'] = $title;
    }

    /**
     * Saving new node.
     * @return int|null
     */
    public function save(): ?int
    {
        Extensions::runHooks('_node_prepare_insert',[&$this]);
        $columns = array_keys($this->data['#node']);
        $placeholders = array_map(function ($field) {
            return ':'.$field;
        },$columns);

        $columns2 = array_map(function ($field) {
            return "`$field`";
        },$columns);

        $con = Database::database();
        $query = $con->prepare("INSERT INTO entity_node_data (".implode(',', $columns2).") VALUES (".implode(',', $placeholders).")");
        foreach ($columns as $field) {
            $query->bindValue(':'.$field, $this->data['#node'][$field]['value']);
        }
        $query->execute();
        $node_id = $con->lastInsertId();
        // Now let's save fields data if node was created
        if(!empty($node_id)) {
            foreach ($this->fields['#fields'] as $field) {
                if($field instanceof FieldInterface) {
                    $field->dataSave($node_id);
                }
            }
            Extensions::runHooks('_node_post_start',[&$this,$node_id]);
            return $node_id;
        }
        return  null;
    }

    /**
     * Deleting node.
     * @return bool True is deleted.
     */
    public function delete(): bool
    {
        Extensions::runHooks('_node_prepare_delete',[&$this]);
        if($this->fields['#fields']) {
            foreach ($this->fields['#fields'] as $field) {
                if($field instanceof FieldInterface) {
                    $field->dataDelete($this->id());
                }
            }
        }
        $query = "DELETE FROM entity_node_data WHERE node_id = :id";
        $statement = Database::database()->prepare($query);
        $statement->bindValue(':id',$this->id());
        $deleted =  $statement->execute();
        Extensions::runHooks('_node_post_delete',[&$this]);
        return $deleted;
    }

    /**
     * Checking if node is deleted.
     * @return bool
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
        Extensions::runHooks('_node_prepare_update',[&$this]);
        $columns = array_keys($this->data['#node']);
        $index = array_filter($columns,function($field){
            return $field === 'node_id';
        });
        if($index) {
            unset($columns[array_keys($index)[0]]);
        }
        $columns2 = array_map(function ($field) {
            return "`$field` = :$field";
        },$columns);

        $con = Database::database();
        $query = $con->prepare("UPDATE entity_node_data SET ".implode(',', $columns2)." WHERE node_id = :id");

        foreach ($columns as $field) {
            $query->bindValue(':'.$field, $this->data['#node'][$field]['value']);
        }
        $query->bindValue(':id',$this->id());
        $flags = [];
        if($query->execute()) {
            foreach ($this->fields['#fields'] as $field) {
                if($field instanceof FieldInterface) {
                    $flags[] = $field->dataUpdate($this->id());
                }
            }
            Extensions::runHooks('_node_post_update',[&$this]);
        }
        return in_array(true,$flags);
    }

    /**
     * Checking if node was updated.
     * @return bool True if was updated.
     */
    public function isUpdated(): bool
    {
        return (int) $this->getValue('updated') > (int) $this->getValue('created');
    }

    /**
     * @param string $entity_name Entity name
     * @return Node|null
     * Node is return
     */
    public static function create(string $entity_name): Node|null
    {
        $entity = new Node();
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
        return $entity->find($node_id);
    }

    /**
     * Getting value of field.
     * @param string $field Field machine name.
     * @return mixed Array if multiple values found.
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
        $statement = Database::database()->prepare($query);
        $statement->bindValue(':id',$this->id());
        return $statement->execute();
    }


    public static function all(): array|false
    {
        $query = Database::database()->prepare("SELECT node_id FROM entity_node_data ORDER BY updated DESC");
        $nodes = $query->execute();
        $nodes = $query->fetchAll();
        if($nodes) {
            foreach ($nodes as $key=>$node) {
                $nodes[$key] = Node::load($node['node_id']);
            }
        }
        return $nodes;
    }

    public static function loadByOwner(int $uid): array
    {
        $query = Database::database()->prepare("SELECT node_id FROM entity_node_data WHERE uid = :uid");
        $query->bindValue(':uid',$uid);
        $query->execute();
        $nodes = $query->fetchAll();
        if($nodes) {
            foreach ($nodes as $key=>$node) {
                $nodes[$key] = Node::load($node['node_id']);
            }
        }
        return $nodes;
    }

}