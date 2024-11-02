<?php

namespace Mini\Cms\Modules\Content\Field;

use Mini\Cms\Modules\Content\Storage\FieldStorage;
use Mini\Cms\Modules\Content\Storage\FieldStorageInterface;
use Mini\Cms\Modules\Content\Tait\ActionTrait;
use PHPMailer\PHPMailer\Exception;

class FieldType implements FieldTypeInterface
{
    use ActionTrait;

    public function __construct(?string $field_name)
    {
        $this->prepare();
        if(!is_null($field_name)) {
            $path = "private://configs/fields/".$field_name.'.yml';
            $this->FIELD = $this->read($path) ?? [];
        }
        else {
            $this->FIELD = [];
        }
    }

    public function getLabel(): string
    {
        return $this->FIELD['#field_label'] ?? '';
    }

    public function getName(): string
    {
        return $this->FIELD['#field_name'] ?? '';
    }

    public function getStorage(): FieldStorageInterface
    {
        return new FieldStorage($this->FIELD['#field_storage']);
    }

    public static function create(array $data): FieldTypeInterface
    {
        return (new FieldType(null))->newField($data);
    }

    private function newField(array $data): static
    {
        $this->FIELD['#field_type'] = $data['type'] instanceof FieldTypeEnum ? $data['type']->value : throw new Exception("Field type is not instance of ".FieldTypeEnum::class);
        $this->FIELD['#field_name'] = $data['name'] ? clean_string($data['name'], replace_char:'_') : throw new Exception("Field name not provided (name)");
        $this->FIELD['#field_label'] = $data['label'] ?? throw new Exception("Field label not provided");
        $this->FIELD['#field_storage'] = $data['storage'] ? clean_string($data['storage'],replace_char:'_') : throw new Exception("storage name not provided");

        return $this;
    }

    public function getType(): string
    {
        return $this->FIELD['#field_type'];
    }

    public function setLabel(string $label): void
    {
       $this->FIELD['#field_label'] = $label;
    }

    public function setName(string $name): void
    {
        $name = clean_string($name, replace_char:'_');
        $this->FIELD['#field_name'] = $name;
    }

    public function setType(FieldTypeEnum $fieldTypeEnum): void
    {
        $this->FIELD['#field_type'] = $fieldTypeEnum->value;
    }

    public function setStorage(string $storage_name): void
    {
        $this->FIELD['#field_storage'] = $storage_name;
    }

    public function save(): bool
    {
        $this->fieldTableCreation();
        $path = "private://configs/fields/".$this->FIELD['#field_name'].'.yml';
        return $this->write($path, $this->FIELD);
    }

    public function update(): bool
    {
        $path = "private://configs/fields/".$this->FIELD['#field_name'].'.yml';
        return $this->overwrite($path, $this->FIELD);
    }

    public function delete(): bool {
        $this->fieldTableDelete();
        $storage = new FieldStorage($this->FIELD['#field_storage']);
        $storage->delete();
        $path = "private://configs/fields/".$this->FIELD['#field_name'].'.yml';
        return $this->remove($path);
    }

    public function fetchData(int $node_id): array
    {
        return $this->fieldData($node_id);
    }

    public function id($value): ?int
    {
        return $this->getFieldDataId($value);
    }

    public function updateFieldData(mixed $old_data, mixed $new_data): bool
    {

        if(is_array($old_data)) {
            $flag = [];
            foreach($old_data as $field => $value) {
                $field_id = $this->getFieldDataId($value);
                if($field_id) {
                    $field[] = $this->updateFieldAction($field_id,$new_data[$field] ?? '');
                }
            }
            return in_array(true,$flag);
        }
        else {
            $field_id = $this->getFieldDataId($old_data);
            if($field_id) {
                return $this->updateFieldAction($field_id,$new_data);
            }
        }
        return false;
    }
}