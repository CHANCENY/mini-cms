<?php

namespace Mini\Cms\Modules\Content\Node;

interface NodeInterface extends NodeEntityInterface
{
    public function bundle(): string;

    public function getTitle(): string;

    public function id(): int;

    public function get(string $key);

    public function set(string $key, $value);

    public function enForceNew(bool $enforce_new = true);

    public function save();
}