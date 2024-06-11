<?php

namespace Mini\Cms\Fields;


use Mini\Cms\Connections\Database\Database;
use Mini\Cms\Fields\FieldViewDisplay\FieldViewDisplayInterface;
use Mini\Cms\Modules\FileSystem\File;
use Mini\Cms\Services\Services;
use Mini\Cms\StorageManager\Connector;
use Mini\Cms\StorageManager\FieldRequirementNotFulFilledException;
use PDO;
use Throwable;

class FileField implements FieldInterface
{

    private array $field = array();

    private mixed $savable_data;

    public function __construct()
    {
        $this->field = [
            'field_type' => 'file',
            'field_settings' => [
                'field_required' => 'NULL',
                'field_size' => 10,
                'field_default_value' => 0,
                'submitter_handler' => 'FileFieldSubmitter',
            ],
        ];
    }
    private Connector $connector;

    public function getType()
    {
        return $this->field['fieldType'] ?? 'file';
    }

    public function getName()
    {
        return $this->field['field_name'] ?? 'file';
    }

    public function getLabel()
    {
        return $this->field['field_label'] ?? 'file';
    }

    public function setLabel($label): void
    {
        $this->field['field_label'] = $label;
    }

    public function getDescription()
    {
        return $this->field['field_description'] ?? null;
    }

    public function setDescription($description): void
    {
        $this->field['field_description'] = $description;
    }

    public function isRequired(): bool
    {
        return !empty($this->field['field_settings']['field_required']) && $this->field['field_settings']['field_required'] === 'NOT NULL';
    }

    public function load(string $field): FieldInterface
    {
        $query = "SELECT * FROM entity_types_fields WHERE field_name = :field_name";
        $statement = Database::database()->prepare($query);
        $statement->execute(['field_name' => $field]);
        $this->field = $statement->fetchAll(\PDO::FETCH_ASSOC)[0] ?? [];
        if(!empty($this->field['field_settings'])) {
            $this->field['field_settings'] = json_decode($this->field['field_settings'], true);
        }
        return $this;
    }

    public function setName(string $name): void
    {
        $clean_name = preg_replace('/[^A-Za-z0-9]/', '_', $name);
        $this->field['field_name'] = 'field_'. strtolower($clean_name);
        $this->setLabel($name);
    }

    public function save(): bool
    {
        $query = "SELECT * FROM entity_types_fields WHERE field_name = :field_name";
        $statement = Database::database()->prepare($query);
        $statement->execute(['field_name' => $this->field['field_name']]);
        $fields = $statement->fetchAll(\PDO::FETCH_ASSOC);

        if(!empty($fields)) {
            return false;
        }

        $fieldColumns = array_keys($this->field);

        $placeholders = '(';
        foreach ($fieldColumns as $fieldColumn) {
            if(empty($this->field[$fieldColumn])) {
                throw new FieldRequirementNotFulFilledException($fieldColumn);
            }
            if(gettype($this->field[$fieldColumn]) == 'array') {
                $this->field[$fieldColumn] = json_encode($this->field[$fieldColumn],JSON_PRETTY_PRINT);
            }
            $placeholders .= ':' . $fieldColumn . ', ';
        }
        $placeholders = trim($placeholders, ', ');
        $placeholders .= ')';

        $query = "INSERT INTO entity_types_fields (".implode(',', $fieldColumns).") VALUES ".$placeholders;

        $statement = Database::database()->prepare($query);
        foreach ($fieldColumns as $fieldColumn) {
            $statement->bindParam(':' . $fieldColumn, $this->field[$fieldColumn]);
        }

        if($statement->execute()) {
            // Making table presentation of field
            $table = "field__".strtolower($this->field['field_name']);
            $size = json_decode($this->field['field_settings'],true)['field_size'] ?? 255;
            $required = json_decode($this->field['field_settings'],true)['field_required'] ?? NULL;
            $database = new Database();
            $field = null;
            if($database->getDatabaseType() === 'sqlite') {
                $field = "field_id INTEGER PRIMARY KEY AUTOINCREMENT, entity_id INT(11), {$table}__value INTEGER $required";
            }
            if($database->getDatabaseType() === 'mysql') {
                $field = "field_id INT(11) PRIMARY KEY AUTO_INCREMENT, entity_id INT(11), {$table}__value INTEGER $required";
            }
            $query = "CREATE TABLE IF NOT EXISTS $table (".$field.")";
            $statement = Database::database()->prepare($query);
            return $statement->execute();
        }
        return false;
    }

    public function setEntityID(int $entityID): void
    {
        $this->field['entity_type_id'] = $entityID;
    }

    public function getFieldDefinition(): array
    {
        return $this->field;
    }

    public function setSize(int $size): void
    {
        $this->field['field_settings']['field_size'] = $size;
    }

    public function setDefaultValue(string $defaultValue): void
    {
        $this->field['field_settings']['field_default_value'] = $defaultValue;
    }

    public function getDefaultValue(): ?string
    {
        return $this->field['field_settings']['field_default_value'] ?? null;
    }

    public function getSize(): int
    {
        return $this->field['field_settings']['field_size'] ?? 255;
    }

    public function setRequired(bool $required): void
    {
        $this->field['field_settings']['field_required'] = $required === true ? 'NOT NULL' : 'NULL';
    }

    public function update(): bool
    {
        $query = Database::database()->prepare("UPDATE entity_types_fields SET field_description = :field_description, field_label = :field_label, field_settings = :field_settings WHERE field_name = :field_name");
        return $query->execute([
            'field_description' => $this->field['field_description'],
            'field_label' => $this->field['field_label'],
            'field_name' => $this->field['field_name'],
            'field_settings'=>json_encode($this->field['field_settings'],JSON_PRETTY_PRINT),
        ]);
    }

    public function delete(): bool
    {
        $table = "field__".$this->field['field_name'];
        try {
            $query = "DELETE FROM entity_types_fields WHERE field_name = :field_name";
            $statement = Database::database()->prepare($query);
            $statement->execute(['field_name' => $this->field['field_name']]);
            $query = Database::database()->prepare("DROP TABLE IF EXISTS $table");
            return $query->execute();
        }catch (Throwable $exception){
            return false;
        }
    }

    public function displayType(): array
    {
        return [
            [
                'label' => 'Image Format',
                'name' => 'image_format',
            ],
            [
                'label' => 'File Format',
                'name' => 'file_format',
            ]
        ];
    }

    public function getDisplayType(): array
    {
        return $this->field['field_settings']['field_display_type'] ?? [
            'label' => 'Image Format',
            'name' => 'image_format',
        ];
    }

    public function markUp(array $field_value): string
    {
        $setting = [
            'label' => $this->field['field_label'],
            'label_visible' => $this->field['field_settings']['field_label_visible'] ?? false,
            'label_name' => $this->getName(),
        ];
        $displayType = $this->getDisplayType();
        $display_name = $displayType['name'];
        $setting['file_display_type'] = $display_name;

        $files = [];
        foreach ($field_value as $field) {
            $file = File::load((int) $field['value']);
            if($file instanceof File) {
                if($display_name === 'image_format') {
                    $files[] = [
                        'uri' => $file->getFilePath(true),
                        'name' => $file->getName(),
                        'size' => $file->getSize(),
                        'width' => $file->getWidth(),
                        'height' => $file->getHeight(),
                    ];
                }
                if($display_name === 'file_format') {
                    $files[] = [
                        'uri' => $file->getFilePath(true),
                        'name' => $file->getName(),
                        'size' => $file->getSize(),
                        'width' => $file->getWidth(),
                        'height' => $file->getHeight(),
                    ];
                }
            }
        }
        return Services::create('render')->render('file_field_display_markup.php',['value' => $files, 'setting' => $setting]);
    }

    public function setDisplayFormat(array $displayFormat): void
    {
        $this->field['field_settings']['field_display_type'] = $displayFormat;
    }

    public function setLabelVisible(bool $visible): void
    {
        $this->field['field_settings']['field_label_visible'] = $visible;
    }

    public function isLabelVisible(): bool
    {
        return $this->field['field_settings']['field_label_visible'] ?? false;
    }

    public function dataSave(int $entity): array|int|null
    {
        $table = "field__".$this->field['field_name'];
        $value_col = "field__".$this->field['field_name'].'__value';
        $flags = [];
        if(is_array($this->savable_data)) {
            foreach($this->savable_data as $value) {
                $value = is_array($value) ? reset($value) : $value;
                $con = Database::database();
                $query = $con->prepare("INSERT INTO $table (`$value_col`,`entity_id`) VALUES (:value, :entity_id)");
                $query->execute(['value' => $value, 'entity_id' => $entity]);
                $flags[] = $con->lastInsertId();
            }
        }else {
            $con = Database::database();
            $query = $con->prepare("INSERT INTO $table (`$value_col`,`entity_id`) VALUES (:value, :entity_id)");
            $query->execute(['value' => $this->savable_data, 'entity_id' => $entity]);
            $flags[] = $con->lastInsertId();
        }
        if(count($flags) === 0) {
            return null;
        }
        elseif (count($flags) === 1) {
            return $flags[0];
        }
        return $flags;
    }

    public function dataUpdate(int $entity): bool
    {
        $table = "field__".$this->field['field_name'];
        $value_col = "field__".$this->field['field_name'].'__value';

        if(is_array($this->savable_data)) {
            $this->savable_data = array_filter(array_map(function ($value) {
                return !empty($value) ? $value : null;
            },$this->savable_data));
        }

        if(empty($this->savable_data)) {
            return false;
        }
        $flags = [];
        if(is_array($this->savable_data)) {
            foreach($this->savable_data as $field) {
                $value = is_array($field) ? reset($value) : $field;
                $query = "SELECT * FROM $table WHERE $value_col = :value AND entity_id = :entity_id";
                $query = Database::database()->prepare($query);
                $query->execute(['value' => $value, 'entity_id' => $entity]);
                if($query->rowCount() <= 0) {
                    $query = "INSERT INTO $table (`$value_col`,`entity_id`) VALUES (:value, :entity_id)";
                    $query = Database::database()->prepare($query);
                    $query->execute(['value' => $value, 'entity_id' => $entity]);
                }
                else {
                    $query = Database::database()->prepare("UPDATE $table SET $value_col = :value WHERE entity_id = :entity");
                    if($query->execute(['value' => $value, 'entity' => $entity])) {
                        $flags[] = true;
                    }
                }
            }
        }
        else {
            $query = "SELECT * FROM $table WHERE $value_col = :value AND entity_id = :entity_id";
            $query = Database::database()->prepare($query);
            $query->execute(['value' => $this->savable_data, 'entity_id' => $entity]);
            if($query->rowCount() <= 0) {
                $query = "INSERT INTO $table (`$value_col`,`entity_id`) VALUES (:value, :entity_id)";
                $query = Database::database()->prepare($query);
                $query->execute(['value' => $this->savable_data, 'entity_id' => $entity]);
            }
            else{
                $query = Database::database()->prepare("UPDATE $table SET $value_col = :value WHERE entity_id = :entity");
                if($query->execute(['value' => $this->savable_data, 'entity' => $entity])) {
                    $flags[] = true;
                }
            }
        }
        return in_array(true, $flags);
    }

    public function dataDelete(int $entity): bool
    {
        $table = "field__".$this->field['field_name'];
        $query = "DELETE FROM $table WHERE entity_id = :entity_id";
        $statement = Database::database()->prepare($query);
        return $statement->execute(['entity_id' => $entity]);
    }

    public function fetchData(int $entity): array
    {
        $table = "field__".$this->field['field_name'];
        $query = Database::database()->prepare("SELECT * FROM $table WHERE entity_id = :entity_id");
        $query->execute(['entity_id' => $entity]);
        $data =  $query->fetchAll(PDO::FETCH_ASSOC);

        $returnable = [];
        foreach ($data as $value) {
            $col = $table.'__value';
            $returnable[] = $value[$col];
        }
        return $returnable;
    }

    public function setData(mixed $data): FieldInterface
    {
        $this->savable_data = $data;
        return $this;
    }
}