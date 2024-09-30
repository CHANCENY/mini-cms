<?php

namespace Mini\Cms\Modules\Content\Field;

enum FieldTypeEnum: string
{
    case TEXT_FIELD = 'text';
    case EMAIL_FIELD = 'email';
    case DATE_FIELD = 'date';
    case FILE_FIELD = 'file';
    case TEXTAREA_FIELD = 'textarea';
    case SELECT_FIELD = 'select';
    case CHECKBOX_FIELD = 'checkboxes';
    case RADIO_FIELD = 'radio';
    case HIDDEN_FIELD = 'hidden';
    case DETAIL_FIELD = 'details';

    case BUTTON_FIELD = 'button';
    case COLOR_FIELD = 'color';
    case DATETIME_LOCAL_FIELD = 'datetime-local';
    case IMAGE_FIELD = 'image';
    case MONTH_FIELD = 'month';
    case NUMBER_FIELD = 'number';
    case PASSWORD_FIELD = 'password';
    case RANGE_FIELD = 'range';
    case RESET_FIELD = 'reset';
    case SEARCH_FIELD = 'search';
    case SUBMIT_FIELD = 'submit';
    case TEL_FIELD = 'tel';
    case TIME_FIELD = 'time';
    case URL_FIELD = 'url';
    case WEEK_FIELD = 'week';

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
