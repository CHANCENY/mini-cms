<?php

namespace Mini\Cms;

use Mini\Cms\Configurations\ConfigFactory;
use Mini\Cms\Connections\Database\Database;
use Mini\Cms\Fields\AddressField;
use Mini\Cms\Fields\EmailField;
use Mini\Cms\Fields\FieldInterface;
use Mini\Cms\Fields\FieldMarkUpInterface;
use Mini\Cms\Fields\FieldViewDisplay\FieldViewDisplayInterface;
use Mini\Cms\Fields\FileField;
use Mini\Cms\Fields\MarkUp\AddressFieldMarkUp;
use Mini\Cms\Fields\MarkUp\EmailFieldMarkUp;
use Mini\Cms\Fields\MarkUp\FileFieldMarkUp;
use Mini\Cms\Fields\MarkUp\PhoneFieldMarkUp;
use Mini\Cms\Fields\MarkUp\ReferenceFieldMarkUp;
use Mini\Cms\Fields\MarkUp\SelectFieldMarkUp;
use Mini\Cms\Fields\MarkUp\TextAreaFieldMarkUp;
use Mini\Cms\Fields\MarkUp\TextFieldMarkUp;
use Mini\Cms\Fields\PhoneField;
use Mini\Cms\Fields\ReferenceField;
use Mini\Cms\Fields\SelectField;
use Mini\Cms\Fields\TextAreaField;
use Mini\Cms\Fields\TextField;
use Mini\Cms\Services\Services;
use ReflectionClass;

class Field
{
    private array $supported_fields;

    public function __construct()
    {
        $this->supported_fields = [
            [
                'label'=> 'Text field',
                'field_type' => 'short_text',
                'field_create_handler' => TextField::class,
                'field_markup_handler' => TextFieldMarkUp::class,
            ],
            [
                'label'=> 'Textarea field',
                'field_type' => 'long_text',
                'field_create_handler' => TextAreaField::class,
                'field_markup_handler' => TextAreaFieldMarkUp::class,
            ],
            [
                'label'=> 'Reference field',
                'field_type' => 'reference',
                'field_create_handler' => ReferenceField::class,
                'field_markup_handler' => ReferenceFieldMarkUp::class,
            ],
            [
                'label'=> 'File field',
                'field_type' => 'file',
                'field_create_handler' => FileField::class,
                'field_markup_handler' => FileFieldMarkUp::class,
            ],
            [
                'label'=> 'Email field',
                'field_type' => 'email',
                'field_create_handler' => EmailField::class,
                'field_markup_handler' => EmailFieldMarkUp::class,
            ],
            [
                'label'=> 'Phone field',
                'field_type' => 'phone',
                'field_create_handler' => PhoneField::class,
                'field_markup_handler' => PhoneFieldMarkUp::class,
            ],
            [
                'label'=> 'Select field',
                'field_type' => 'select',
                'field_create_handler' => SelectField::class,
                'field_markup_handler' => SelectFieldMarkUp::class,
            ],
            [
                'label'=> 'Address field',
                'field_type' => 'address',
                'field_create_handler' => AddressField::class,
                'field_markup_handler' => AddressFieldMarkUp::class,
            ]
        ];

        $config = Services::create('config.factory');
        if($config instanceof ConfigFactory) {
            $custom = $config->get('supported_fields') ?? [];
            $this->supported_fields = array_merge($this->supported_fields, $custom);
        }

        foreach($this->supported_fields as $key=>$field) {
            if(!isset($field['field_type'])) {
               unset($this->supported_fields[$key]);
            }

            if((new $field['field_create_handler']() instanceof FieldInterface) === false) {
                unset($this->supported_fields[$key]);
            }

            if((new $field['field_markup_handler']() instanceof FieldMarkUpInterface) === false) {
                unset($this->supported_fields[$key]);
            }
        }
    }

    public function getSupportedFields(): array
    {
        foreach($this->supported_fields as $key=>&$field) {
            $f = Field::create($field['field_type']);
            if($f instanceof FieldViewDisplayInterface) {
                $field['display_settings'] = $f->displayType();
            }
        }
        return $this->supported_fields;
    }
    public function addSupportedField(array $field): void
    {
        $this->supported_fields[] = $field;
    }

    public static function fields(): array
    {
        return (new static())->getSupportedFields();
    }

    public function findFieldCreator(string $field_type): ?FieldInterface
    {
        if(!empty($this->supported_fields)) {
            $fieldConfiguration = array_filter($this->supported_fields, function($field) use ($field_type) {
                return $field['field_type'] === $field_type;
            });

            if(!empty($fieldConfiguration)) {
                $field = reset($fieldConfiguration);

                if(isset($field['field_create_handler']) && class_exists($field['field_create_handler'])) {
                    return new $field['field_create_handler']();
                }
            }
        }
        return null;
    }

    public function findFieldMarkUp(string $field_type): ?FieldMarkUpInterface
    {
        if(!empty($this->supported_fields)) {
            $fieldConfiguration = array_filter($this->supported_fields, function($field) use ($field_type) {
                return $field['field_type'] === $field_type;
            });

            if(!empty($fieldConfiguration)) {
                $field = reset($fieldConfiguration);

                if(isset($field['field_markup_handler']) && class_exists($field['field_markup_handler'])) {
                    return new $field['field_markup_handler']();
                }
            }
        }
        return null;
    }


    public static function create(string $field_type): ?FieldInterface
    {
        return (new static())->findFieldCreator($field_type);
    }

    public static function markUp(string $field_type): ?FieldMarkUpInterface
    {
        return (new static())->findFieldMarkUp($field_type);
    }

    public function find(string $field_name): ?FieldInterface
    {
        $qeury = Database::database()->prepare("SELECT * FROM `entity_types_fields` WHERE `field_name` = :field_name");
        $qeury->bindParam(':field_name', $field_name);
        $qeury->execute();
        $result = $qeury->fetch();
        if (empty($result)) {
            return null;
        }

        if(!empty($result['field_type'])) {
            $field_type = $result['field_type'];
            $fieldConfiguration = array_filter($this->supported_fields, function($field) use ($field_type) {
                return $field['field_type'] === $field_type;
            });

            if(!empty($fieldConfiguration)) {
                $field = reset($fieldConfiguration);
                if(isset($field['field_create_handler']) && class_exists($field['field_create_handler'])) {
                    return (new $field['field_create_handler']())->load($field_name);
                }
            }
        }
        return null;
    }


    public static function load(string $field_name): ?FieldInterface
    {
        return (new static())->find($field_name);
    }

}