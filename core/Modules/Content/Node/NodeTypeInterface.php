<?php

namespace Mini\Cms\Modules\Content\Node;

use Mini\Cms\Modules\Content\Field\FieldTypeInterface;

interface NodeTypeInterface
{
    public function getLabel(): string;

    public function getTypeName(): string;

    public function getDescription(): string;

    public static function create(array $data): ?NodeTypeInterface;

    public function save(): bool;

    public function getFields(): array;

    public function getField(string $field_name): FieldTypeInterface;

    public function setName(string $name): void;

    public function setLabel(string $label): void;

    public function setDescription(string $description): void;

    public function setField(string $field_name): void;
}