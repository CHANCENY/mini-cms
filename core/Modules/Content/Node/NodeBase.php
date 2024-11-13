<?php

namespace Mini\Cms\Modules\Content\Node;

use Mini\Cms\Connections\Database\Database;
use Mini\Cms\Connections\Database\Queries\QueryManager;
use Mini\Cms\Entities\Node;
use Mini\Cms\Mini;
use Mini\Cms\Modules\Content\Field\FieldType;
use Mini\Cms\Modules\CurrentUser\CurrentUser;
use Throwable;

class NodeBase implements NodeInterface
{
    protected array $node;
    protected bool $enforce_new = false;

    protected array $fields;

    protected NodeType $nodeType;
    protected QueryManager $query_manager;

    public function __construct(?int $nid = null)
    {
        $this->node = [];
        $this->query_manager = new QueryManager(Database::database());
        if($nid) {
            $this->query_manager->select("node_field_data", 'n');
            $this->query_manager->addCondition("nid", $nid);
            $statement = $this->query_manager->execute();
            $node = $statement->fetch();
            if($node) {
                foreach ($node as $field => $value) {
                    $this->node['values']['#'.$field]['value'] = $value;
                }
                $node_type = new NodeType($node['type']);
                $this->nodeType = $node_type;
                $fields = $node_type->getFields();
                $this->fields = $fields;
                foreach($fields as $field) {
                    if($field instanceof FieldType) {
                        $this->node['values']['#'.$field->getName()]['value'] = $field->fetchData($nid);
                    }
                }
            }
        }
    }

    public function bundle(): string
    {
        return $this->node['values']['#type']['value'] ?? '';
    }

    public function getTitle(): string
    {
       return $this->node['values']['#title']['value'] ?? '';
    }

    public function id(): int
    {
        return $this->node['values']['#nid']['value'] ?? 0;
    }

    public function get(string $key)
    {
        return $this->node['values']["#$key"]['value'] ?? null;
    }

    public function set(string $key, $value)
    {
        $this->node['values']["#$key"]['value'] = $value;
    }

    public function enForceNew(bool $enforce_new = true): void
    {
        $this->enforce_new = $enforce_new;
    }

    public function save()
    {
        $data_new = $this->node['values'];
        if(empty($this->id())) {
            $this->query_manager->insert("node_field_data");
            $this->query_manager->addField("title", $data_new['#title']['value']);
            $this->query_manager->addField("type", $data_new['#type']['value']);
            $this->query_manager->addField("status", $data_new['#status']['value']);
            $this->query_manager->addField("langcode", $data_new['#langcode']['value']);
            $this->query_manager->addField("uid", $data_new['#uid']['value']);
            $this->query_manager->addField("created", $data_new['#created']['value']);
            $this->query_manager->addField("changed", $data_new['#changed']['value']);
            $this->query_manager->addField("vid", $data_new['#vid']['value']);
            $this->query_manager->execute();
            $nid = $this->query_manager->lastInsertId();
            if($nid) {
               foreach ($data_new as $key => $value) {
                   try{
                       $field_type = $this->nodeType->getField(trim($key, '#'));
                       $field_type->createFieldData($nid, $value['value']);
                   }catch (Throwable $exception) {}
               }
               Mini::messenger()->addSuccessMessage($this->getTitle()." content created");
            }
        }
        else {
            $node =Node::load((int)$this->id());
            $this->query_manager->update("node_field_data");
            $this->query_manager->addField("title", $data_new['#title']['value']);
            $this->query_manager->addField("type", $data_new['#type']['value']);
            $this->query_manager->addField("status", $data_new['#status']['value']);
            $this->query_manager->addField("langcode", $data_new['#langcode']['value']);
            $this->query_manager->addField("uid", $data_new['#uid']['value']);
            $this->query_manager->addField("changed", $data_new['#changed']['value']);
            $this->query_manager->addField("vid", $data_new['#vid']['value']);
            $this->query_manager->addCondition("nid", $node->id());
            $stm = $this->query_manager->execute();
            dump($stm->queryString);
            exit;
        }
    }

    public static function create(string $node_type, array $data): static
    {
        $node_type = new NodeType($node_type);
        if(empty($node_type->getTypeName())) {
            throw new \Exception("Node type given not found");
        }
        $node = new static();
        $node->set('type', $node_type->getTypeName());
        if(empty($data['title'])) {
            throw new \Exception("Title given not found");
        }
        $node->set('title', $data['title']);
        $node->set('langcode', $data['langcode'] ?? 'en');
        $node->set('created', time());
        $node->set('changed', time());
        $node->set('uid', (new CurrentUser())->id());
        $node->set('vid', 0);
        $published = 0;
        if(!empty(trim($data['published']))) {
            $published = 1;
        }
        $node->set('status', $published);
        $fields = $node_type->getFields();
        foreach($fields as $field) {
            if($field instanceof FieldType) {
                if($value = $data[$field->getName()]) {
                    $node->set($field->getName(), $value);
                }
            }
        }
        $node->setNodeType($node_type);
        $node->setFields($fields);
        return $node;
    }

    public static function load(int $nid): static
    {
       return new static($nid);
    }

    public function getFields(): array
    {
        return $this->fields;
    }

    public function setFields(array $fields): void
    {
        $this->fields = $fields;
    }

    public function getNodeType(): NodeType
    {
        return $this->nodeType;
    }

    public function setNodeType(NodeType $nodeType): void
    {
        $this->nodeType = $nodeType;
    }

    /**
     * @throws \Exception
     */
    public static function loadMultiple(array $ids = [], string $bundle = ''): array|bool
    {
        $query = new QueryManager(Database::database());
        $query->select("node_field_data","n");
        $query->selectFields(["nid"]);
        if(!empty($ids) && !empty($bundle)) {
            $query->addCondition("type", $bundle);
            $query->addCondition("nid", $ids, "IN");
        }
        elseif (!empty($ids)) {
            $query->addCondition("nid", $ids, "IN");
        }
        elseif (!empty($bundle)) {
            $query->addCondition("type", $bundle);
        }
        $statement = $query->execute();
        return $statement->fetchAll();
    }
}