<?php

namespace Mini\Cms\Modules\Content\Field;

use Mini\Cms\Modules\Content\Storage\FieldStorageInterface;

interface FieldTypeInterface
{
    public function getLabel(): string;

    public function getName(): string;

    public function getStorage(): FieldStorageInterface;

    public static function create(array $data): FieldTypeInterface;

    public function getType(): string;

    public function setLabel(string $label): void;

    public function setName(string $name): void;

    public function setType(FieldTypeEnum $fieldTypeEnum): void;

    public function setStorage(string $storage_name): void;

    public function save(): bool;
}