<?php

namespace Mini\Cms\Modules\Content\Node;

use Mini\Cms\Connections\Database\Database;
use Mini\Cms\Connections\Database\Queries\QueryManager;
use Mini\Cms\Fields\FieldInterface;
use Mini\Cms\Modules\Content\Node\NodeInterface;

class NodeBase implements NodeInterface
{

    protected array $NODE_DATA;

    protected bool $enforce_new = false;

    protected QueryManager $query_manager;

    public function __construct(?int $nid = null)
    {
        $this->NODE_DATA = [];
        $this->query_manager = new QueryManager(Database::database());
        if($nid) {
            $this->query_manager->select("node_field_data", 'n');
            $this->query_manager->addCondition("n.nid", $nid);
            $statement = $this->query_manager->execute();
            $node = $statement->fetch();
            if($node) {
                $node_type = new NodeType($node['type']);
                $this->NODE_DATA['#entity_type'] = $node_type;
                $fields = $node_type->getFields();
                $this->NODE_DATA['#fields'] = $fields;
                foreach($fields as $field) {
                    if($field instanceof FieldInterface) {
                        $this->NODE_DATA['node']['values']['#'.$field->getName()]['value'] = $field->queryData($nid);
                    }
                }
            }
        }
    }

    public function bundle(): string
    {
        return $this->NODE_DATA['node']['#type']['value'];
    }

    public function getTitle(): string
    {
       return $this->NODE_DATA['node']['values']['#title']['value'];
    }

    public function id(): int
    {
        return $this->NODE_DATA['node']['values']['#id']['value'];
    }

    public function get(string $key)
    {
        return $this->NODE_DATA['node']['values']["#$key"]['value'] ?? null;
    }

    public function set(string $key, $value)
    {
        $this->NODE_DATA['node']['values']["#$key"]['value'] = $value;
    }

    public function enForceNew(bool $enforce_new = true): void
    {
        $this->enforce_new = $enforce_new;
    }

    public function save()
    {
        // TODO: Implement save() method.
    }
}