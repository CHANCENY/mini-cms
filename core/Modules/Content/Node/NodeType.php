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
        $this->CONTENT_TYPE = [];
        $this->prepare();
        if(!is_null($content_type)) {
            $content_file = 'private://configs/types/'.$content_type.'.yml';
            $this->CONTENT_TYPE = $this->read($content_file);
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

    public function getContentTypes(): ?array
    {
        $list_content_types = scandir('private://configs/types');

        if($list_content_types) {
            $list_content_types = array_diff($list_content_types,['.','..']);
            foreach ($list_content_types as $key=>$content_type) {
                $content_l = explode('.', $content_type);
                $content_name = $content_l[0];
                $list_content_types[$key] = new NodeType(trim($content_name));
            }
        }
        return $list_content_types;
    }

    public static function loadTypes(): ?array
    {
        return (new self(null))->getContentTypes();
    }
}