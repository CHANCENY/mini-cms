<?php

namespace Mini\Cms\Modules\Content\Node;

abstract class NodeBase implements NodeInterface
{

    protected array $node;

    private bool $enforce_new;

    public function id(): int
    {
        return $this->node['values']['#nid']['value'];
    }

    public function getTitle(): string
    {
        return $this->node['values']['#title']['value'];
    }

    /**
     * @throws NodeFieldNotExistException
     */
    public function get(string $key): mixed
    {
        return $this->node['values'][$key] ?? throw new NodeFieldNotExistException("Field {$key} does not exist.");
    }

    public function set(string $key, mixed $value): mixed
    {
       $this->node['values'][$key]['values'] = $value;
       return $this;
    }

    public function enforceNew(bool $isNew): NodeInterface
    {
        $this->enforce_new = $isNew;
        return $this;
    }

    public function save(): mixed
    {
        // TODO: Implement save() method.
    }

    public static function create(array $data): NodeInterface
    {
        $node = new static();
        foreach ($data as $field => $value) {
            $node->set($field, $value);
        }
        return $node;
    }

    public function find(int $id): ?NodeInterface
    {

    }

    public static function load(int $id): NodeInterface
    {

    }

    public function isPublished(): bool
    {
        // TODO: Implement isPublished() method.
    }

    public function setPublished(bool $published): NodeInterface
    {
        // TODO: Implement setPublished() method.
    }
}