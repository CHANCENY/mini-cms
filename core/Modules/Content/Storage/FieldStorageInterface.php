<?php

namespace Mini\Cms\Modules\Content\Storage;

use Mini\Cms\Modules\Content\Field\FieldTypeEnum;

interface FieldStorageInterface
{
    public function getStorageName(): string;

    public function getStorageType(): string;

    public function getSize(): int;

    public function getDefault(): string|bool|null|int|float;

    public function isNullable(): bool;

    public function isMultipleAllowed(): bool;

    public function getMultipleLimit(): int;

    public function setSize(int $size): void;

    public function setDefault(mixed $default): void;

    public function setMultiple(bool $is_multiple, int $count): void;

    public function setStorageType(FieldTypeEnum $type): void;

    public function setIsNullable(): void;

    public function save(): bool;

    public function setStorageName(string $name): void;
}