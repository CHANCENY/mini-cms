<?php

namespace Mini\Cms\Modules\Content\Storage;

use Mini\Cms\Modules\Content\Field\FieldTypeEnum;
use Mini\Cms\Modules\Content\Tait\ActionTrait;

class FieldStorage implements FieldStorageInterface
{

    use ActionTrait;
    public function __construct(?string $field_storage)
    {
        $this->prepare();
        if(!is_null($field_storage)) {
            $path = 'private://configs/storages/'.$field_storage.'.yml';
            $data = $this->read($path);
            $this->STORAGE = $data ?? [];
        }
        else {
            $this->STORAGE = [];
        }
    }

    public function getStorageName(): string
    {
        return $this->STORAGE['#storage_name'];
    }

    public function getStorageType(): string
    {
        return $this->STORAGE['#storage_type'];
    }

    public function getSize(): int
    {
        return $this->STORAGE['##settings']['#size'];
    }

    public function getDefault(): string|bool|null|int|float
    {
        return $this->STORAGE['#settings']['#default_value'];
    }

    public function isNullable(): bool
    {
        return $this->STORAGE['#settings']['#is_nullable'];
    }

    public function isMultipleAllowed(): bool
    {
        return $this->STORAGE['#settings']['#is_multiple'];
    }

    public function getMultipleLimit(): int
    {
        return $this->STORAGE['#settings']['#multiple_limit'] ?? 1;
    }

    public function setSize(int $size): void
    {
        $this->STORAGE['#settings']['#size'] = $size;
    }

    public function setDefault(mixed $default): void
    {
        $this->STORAGE['#settings']['#default_value'] = $default;
    }

    public function setMultiple(bool $is_multiple, int $count): void
    {
        $this->STORAGE['#settings']['#is_multiple'] = $is_multiple;
        $this->STORAGE['#settings']['#multiple_limit'] = $count;
    }

    public function setStorageType(FieldTypeEnum $type): void
    {
        $this->STORAGE['#storage_type'] = $type->value;
    }

    public function setIsNullable(): void
    {
        $this->STORAGE['#settings']['#is_nullable'] = true;
    }

    public function save(): bool
    {
        $path = 'private://configs/storages/'.$this->STORAGE['#storage_name'].'.yml';
       return $this->write($path,$this->STORAGE);
    }

    public function setStorageName(string $name): void
    {
        $name = clean_string($name, replace_char:'_');
        $this->STORAGE['#storage_name'] = $name;
    }
}