<?php

namespace Mini\Cms\Modules\Content\Node;

interface NodeEntityInterface
{
    public static function create(string $node_type, array $data): static;

    public static function load(int $nid): static;

    public static function loadMultiple(array $ids = [], string $bundle = ''): array|bool;
}