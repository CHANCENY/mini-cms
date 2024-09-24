<?php

namespace Mini\Cms\Modules\Content\Node;

interface NodeInterface
{

    /**
     * Returns id.
     * @return int
     */
    public function id(): int;
    /**
     * Return the node title.
     * @return string
     */
    public function getTitle(): string;

    /**
     * Returns the field data.
     * @param string $key
     * @return mixed
     */
    public function get(string $key): mixed;

    /**
     * @param string $key
     * @param mixed $value
     * @return NodeInterface
     */
    public function set(string $key, mixed $value): NodeInterface;

    /**
     * enforce new.
     * @param bool $isNew
     * @return NodeInterface
     */
    public function enforceNew(bool $isNew): NodeInterface;

    /**
     * Save the node or update
     * @return mixed
     */
    public function save(): mixed;

    /**
     * Create node.
     * @param array $data
     * @return NodeInterface
     */
    public static function create(array $data): NodeInterface;

    /**
     * Load node
     * @param int $id
     * @return NodeInterface
     */
    public static function load(int $id): NodeInterface;

    /**
     * Is the node published?
     * @return bool
     */
    public function isPublished(): bool;

    /**
     * Set published status.
     * @param bool $published
     * @return NodeInterface
     */
    public function setPublished(bool $published): NodeInterface;

    /**
     * Loading node.
     * @param int $id
     * @return NodeInterface|null
     */
    public function find(int $id): ?NodeInterface;
}