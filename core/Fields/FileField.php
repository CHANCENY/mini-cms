<?php

namespace Mini\Cms\Fields;


use Mini\Cms\Connections\Database\Database;
use Mini\Cms\StorageManager\Connector;
use Mini\Cms\StorageManager\FieldRequirementNotFulFilledException;

class FileField implements FieldInterface
{

    private array $field = array();

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
        $query = Database::database()->prepare("UPDATE entity_types_fields SET field_description = :field_description, field_label = :field_label WHERE field_name = :field_name");
        return $query->execute([
            'field_description' => $this->field['field_description'],
            'field_label' => $this->field['field_label'],
            'field_name' => $this->field['field_name'],
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
}