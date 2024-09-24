<?php

namespace Mini\Cms\Modules\Content\Node;

use Mini\Cms\Modules\Content\Field\FieldTypeInterface;
use Mini\Cms\Modules\Content\Tait\ActionTrait;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;

class NodeType implements NodeTypeInterface
{

    use ActionTrait;

    public function __construct(?string $content_type)
    {
        $this->prepare();
        if(!is_null($content_type)) {

        }
    }

    public function getLabel(): string
    {
        return $this->CONTENT_TYPE['#content_label'] ?? '';
    }

    public function getTypeName(): string
    {
        return $this->CONTENT_TYPE['#content_name'] ?? '';
    }

    public function getDescription(): string
    {
        return $this->CONTENT_TYPE['#content_description'] ?? '';
    }

    public static function create(array $data): ?NodeTypeInterface
    {
        try{
            if(isset($data['name']) && $data['label']) {
                $type = new static(null);
                $type->setName($data['name']);
                $type->setLabel($data['label']);
                if($data['fields']) {
                    foreach ($data['fields'] as $field) {
                        $type->setField($field);
                    }
                }
                return $type;
            }
        }catch (\Throwable) {}
        return null;
    }

    public function save(): bool
    {
        $content_file = "private://configs/types/".$this->CONTENT_TYPE['#content_name'].".yml";
        return $this->write($content_file, $this->CONTENT_TYPE);
    }

    public function getFields(): array
    {
        return $this->CONTENT_TYPE['#fields'] ?? [];
    }

    public function getField(string $field_name): FieldTypeInterface
    {
        return $this->CONTENT_TYPE['#fields'][$field_name] ?? throw new FileNotFoundException("Field $field_name not found");
    }

    public function setName(string $name): void
    {
        $this->CONTENT_TYPE['#content_name'] = clean_string($name, replace_char:'_');
    }

    public function setLabel(string $label): void
    {
        $this->CONTENT_TYPE['#content_label'] = $label;
    }

    public function setDescription(string $description): void
    {
        $this->CONTENT_TYPE['#content_description'] = $description;
    }

    public function setField(string $field_name): void
    {
        $this->CONTENT_TYPE['#fields'][] = clean_string($field_name,replace_char:'_');
    }
}