<?php

namespace Mini\Cms\Modules\Content\Field;

enum FieldTypeEnum: string
{
    case TEXT_FIELD = 'varchar';

    public static function getAllNames(): array {
        return array_column(self::cases(), 'name');
    }

    public static function getValues(): array {
        return array_column(self::cases(), 'value');
    }

    public static function getAll(): array
    {
        return self::cases();
    }

    public static function get(string $field_type): FieldTypeEnum|false|null
    {
        $field = array_filter(self::getAll(),function($item) use ($field_type){
            return $item->value === $field_type;
        });
        if(empty($field)) {
            return null;
        }
        return reset($field);
    }
}
