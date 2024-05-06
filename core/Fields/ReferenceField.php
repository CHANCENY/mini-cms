<?php

namespace Mini\Cms\Fields;

use Chance\Entity\StorageManager\Connector;
use Chance\Entity\StorageManager\FieldRequirementNotFulFilledException;

class ReferenceField implements FieldInterface
{
    private array $field = array();
    private Connector $connector;

    public function __construct()
    {
        $this->field = [
            'field_type' => 'reference',
            'field_settings' => [
                'field_required' => 'NULL',
                'field_size' => 11,
                'field_default_value' => null,
            ],
        ];
    }

    public function getType(): string
    {
        return $this->field['field_type'] ?? 'text';
    }

    public function getName(): ?string
    {
        return $this->field['field_name'] ?? null;
    }

    public function getLabel(): ?string
    {
        return $this->field['field_label'] ?? null;
    }

    public function setLabel($label): void
    {
        $this->field['field_label'] = $label;
    }

    public function getDescription(): ?string
    {
        return $this->field['field_description'] ?? null;
    }


    public function setDescription($description): void
    {
        $this->field['field_description'] = $description;
    }

    public function isRequired(): false
    {
        return $this->field['field_settings']['field_required'] === 'NOT NULL';
    }

    public function load(string $field): void
    {
        if(!isset($this->connector)) {
            $this->connector = new Connector();
        }
        $query = "SELECT * FROM entity_types_fields WHERE field_name = :field_name";
        $statement = $this->connector->getConnection()->prepare($query);
        $statement->execute(['field_name' => $field]);
        $this->field = $statement->fetchAll(\PDO::FETCH_ASSOC)[0] ?? [];
    }

    public function setName(string $name): void
    {
        $clean_name = preg_replace('/[^A-Za-z0-9]/', '_', $name);
        $this->field['field_name'] = 'field_'. strtolower($clean_name);
        $this->setLabel($name);
    }

    public function save(): bool
    {
        if(!isset($this->connector)) {
            $this->connector = new Connector();
        }

        $query = "SELECT * FROM entity_types_fields WHERE field_name = :field_name";
        $statement = $this->connector->getConnection()->prepare($query);
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

        $statement = $this->connector->getConnection()->prepare($query);
        foreach ($fieldColumns as $fieldColumn) {
            $statement->bindParam(':' . $fieldColumn, $this->field[$fieldColumn]);
        }

        if($statement->execute()) {
            // Making table presentation of field
            $table = "field__".strtolower($this->field['field_name']);
            $size = json_decode($this->field['field_settings'],true)['field_size'] ?? 255;
            $required = json_decode($this->field['field_settings'],true)['field_required'] ?? NULL;
            $field = "field_id INTEGER PRIMARY KEY AUTOINCREMENT, entity_id INT(11), {$table}__value INTEGER($size) $required";
            $query = "CREATE TABLE IF NOT EXISTS $table (".$field.")";
            $statement = $this->connector->getConnection()->prepare($query);
            return $statement->execute();
        }
        return false;
    }

    public function connector(Connector $connector): void
    {
        $this->connector = $connector;
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
}