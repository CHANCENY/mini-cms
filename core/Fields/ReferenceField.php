<?php

namespace Mini\Cms\Fields;


use Mini\Cms\Connections\Database\Database;
use Mini\Cms\Entities\Node;
use Mini\Cms\Fields\FieldViewDisplay\FieldViewDisplayInterface;
use Mini\Cms\Services\Services;
use Mini\Cms\StorageManager\FieldRequirementNotFulFilledException;
use Mini\Cms\Vocabulary;
use Throwable;

class ReferenceField implements FieldInterface, FieldViewDisplayInterface
{
    private array $field = array();

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
                $field = "field_id INTEGER PRIMARY KEY AUTOINCREMENT, entity_id INT(11), {$table}__value INTEGER($size) $required";
            }
            if($database->getDatabaseType() === 'mysql') {
                $field = "field_id INT(11) PRIMARY KEY AUTO_INCREMENT, entity_id INT(11), {$table}__value INTEGER($size) $required";
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

    public function setReferenceSettings(array $reference_settings): void
    {
        $this->field['field_settings']['reference_settings'] = $reference_settings;
    }
    
    public function getReferenceSettings(): array
    {
        return $this->field['field_settings']['reference_settings'] ?? [];
    }
    
    public function referenceResults(string $search_string): array|false
    {
        $settings = $this->getReferenceSettings();
        $query = null;
        $ref_name = null;
        if($settings['reference_type'] === 'entity') {
            $ref_name = $settings['reference_name'];
            $query = "SELECT title AS name,node_id AS id FROM entity_node_data WHERE bundle = :id AND title LIKE '%$search_string%' LIMIT 10";
        }
        elseif ($settings['reference_type'] === 'vocabulary') {
            $voc = Vocabulary::vocabulary($settings['reference_name']);
            $ref_name = $voc->vid();
            $query = "SELECT term_name AS name, FROM terms WHERE vocabulary_id = :id AND term_name LIKE '%$search_string%' LIMIT 10";
        }
        $query = Database::database()->prepare($query);
        $query->bindParam(':id', $ref_name);
        $query->execute();
        return $query->fetchAll(\PDO::FETCH_ASSOC);
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

    public function setDisplayFormat(array $displayFormat): void
    {
        $this->field['field_settings']['field_display_type'] = $displayFormat;
    }

    public function setLabelVisible(bool $visible): void
    {
        $this->field['field_settings']['field_label_visible'] = $visible;
    }

    public function displayType(): array
    {
        return [
            [
                'label' => 'Link',
                'name' => 'link',
            ],
            [
                'label' => 'Text',
                'name' => 'text',
            ]
        ];
    }

    public function getDisplayType(): array
    {
        return $this->field['field_settings']['field_display_type'] ?? [
            'label' => 'Link',
            'name' => 'link',
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
        $field_value = reset($field_value);

        $set = $this->getReferenceSettings();


        if($set['reference_type'] === 'entity') {

            $node = Node::load((int) $field_value['value']);
            if($node instanceof Node) {
                if($display_name === 'link') {
                    $field_value = "<a class='link' title='{$node->getTitle()}' href='/structure/content/node/{$node->id()}'>{$node->getTitle()}</a>";
                }
                elseif ($display_name === 'text') {
                    $field_value = "<p>{$node->getTitle()}</p>";
                }
            }
        }
        elseif ($set['reference_type'] === 'vocabulary') {
            //TODO: handle terms here.
        }
        return Services::create('render')->render('reference_field_display_markup.php',['value' => $field_value, 'setting' => $setting]);
    }

    public function isLabelVisible(): bool
    {
        return $this->field['field_settings']['field_label_visible'] ?? false;
    }
}