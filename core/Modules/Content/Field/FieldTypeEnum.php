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
}
