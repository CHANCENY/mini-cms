<?php

namespace Mini\Cms\Fields;

use Mini\Cms\Connections\Database\Database;
use Mini\Cms\StorageManager\FieldRequirementNotFulFilledException;

class AddressField implements FieldInterface
{

    private array $field = [];
    private mixed $savable_data;

    public function __construct()
    {
        $this->field = [
            'field_type' => 'address',
            'field_settings' => [
                'field_required' => 'NULL',
                'field_size' => 255,
                'field_default_value' => null,
            ],
        ];
    }
    /**
     * @inheritDoc
     */
    public function getType()
    {
        return $this->field['field_type'] ?? 'address';
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return $this->field['field_name'] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function getLabel()
    {
        return $this->field['field_label'] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function setLabel($label): void
    {
        $this->field['field_label'] = $label;
    }

    /**
     * @inheritDoc
     */
    public function getDescription()
    {
        return $this->field['field_description'] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function setDescription($description): void
    {
        $this->field['field_description'] = $description;
    }

    /**
     * @inheritDoc
     */
    public function isRequired()
    {
        return !empty($this->field['field_settings']['field_required']) && $this->field['field_settings']['field_required'] === 'NOT NULL';
    }

    /**
     * @inheritDoc
     */
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

    /**
     * @inheritDoc
     */
    public function setName(string $name): void
    {
        $clean_name = preg_replace('/[^A-Za-z0-9]/', '_', $name);
        $this->field['field_name'] = 'field_'. strtolower($clean_name);
        $this->setLabel($name);
    }

    /**
     * @inheritDoc
     */
    public function save()
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
                $field = "field_id INTEGER PRIMARY KEY AUTOINCREMENT, entity_id INT(11), {$table}__value varchar($size) $required";
            }
            if($database->getDatabaseType() === 'mysql') {
                $field = "field_id INT(11) PRIMARY KEY AUTO_INCREMENT, entity_id INT(11), {$table}__value varchar($size) $required";
            }
            $query = "CREATE TABLE IF NOT EXISTS $table (".$field.")";
            $statement = Database::database()->prepare($query);
            return $statement->execute();
        }
        return false;
    }

    /**
     * @inheritDoc
     */
    public function setEntityID(int $entityID)
    {
        $this->field['entity_type_id'] = $entityID;
    }

    /**
     * @inheritDoc
     */
    public function getFieldDefinition()
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

    /**
     * @inheritDoc
     */
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

    /**
     * @inheritDoc
     */
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

    /**
     * @inheritDoc
     */
    public function setDisplayFormat(array $displayFormat): void
    {
        $this->field['field_settings']['field_display_type'] = $displayFormat;
    }

    /**
     * @inheritDoc
     */
    public function setLabelVisible(bool $visible): void
    {
        $this->field['field_settings']['field_label_visible'] = $visible;
    }

    /**
     * @inheritDoc
     */
    public function isLabelVisible(): bool
    {
        return $this->field['field_settings']['field_label_visible'] ?? false;
    }

    /**
     * @inheritDoc
     */
    public function dataSave(int $entity): array|int|null
    {
        // TODO: Implement dataSave() method.
        return null;
    }

    /**
     * @inheritDoc
     */
    public function dataUpdate(int $entity): bool
    {
        // TODO: Implement dataUpdate() method.
        return true;
    }

    /**
     * @inheritDoc
     */
    public function dataDelete(int $entity): bool
    {
        // TODO: Implement dataDelete() method.
        return true;
    }

    /**
     * @inheritDoc
     */
    public function fetchData(int $entity): array
    {
        // TODO: Implement fetchData() method.
        return [];
    }

    /**
     * @inheritDoc
     */
    public function setData(mixed $data): FieldInterface
    {
        // TODO: Implement setData() method.
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function displayType(): array
    {
        return [
            [
                'label' => 'Trimmed',
                'name' => 'trimmed',
            ],
            [
                'label' => 'Full Text',
                'name' => 'full_text',
            ]
        ];
    }

    /**
     * @inheritDoc
     */
    public function getDisplayType(): array
    {
        return $this->field['field_settings']['field_display_type'] ?? [
            'label' => 'Trimmed',
            'name' => 'trimmed',
        ];
    }

    /**
     * @inheritDoc
     */
    public function markUp(array $field_value): string
    {
       return '';
    }
}