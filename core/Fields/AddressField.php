<?php

namespace Mini\Cms\Fields;

use Mini\Cms\Connections\Database\Database;
use Mini\Cms\Modules\Respositories\Territory\AddressFormat;
use Mini\Cms\Modules\Respositories\Territory\City;
use Mini\Cms\Modules\Respositories\Territory\Country;
use Mini\Cms\Modules\Respositories\Territory\State;
use Mini\Cms\Services\Services;
use Mini\Cms\StorageManager\FieldRequirementNotFulFilledException;
use PDO;

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
        $fields = AddressFormat::fieldNames();
        // Swapping field keys
        $new_data = [];
        foreach($this->savable_data as $key => $value) {
            if(isset($fields[$key])) {
                $field = $fields[$key];
                $new_data[$field['storage_field_name']] = $value;
            }
        }
        $new_data['country_code'] = $this->savable_data['country'] ?? null;
        if(!empty($new_data['country_code'])) {
            $this->savable_data = $new_data;
        }

        // Query line building.
        $placeholders = array_map(function ($field) {
            return ":$field";
        },array_keys($this->savable_data));

        $con = Database::database();
        $query_line = "INSERT INTO address_fields_data (".implode(', ', array_keys($this->savable_data)).") VALUES (".implode(', ', $placeholders).")";
        $query = $con->prepare($query_line);
        foreach ($this->savable_data as $key => $value) {
          $query->bindValue(":$key", $value);
        }
        $query->execute();
        $address_id = $con->lastInsertId();

        // Inserting referencing id in field table
        if($address_id) {
            $table = "field__".$this->field['field_name'];
            $value_col = "field__".$this->field['field_name'].'__value';
            $query = $con->prepare("INSERT INTO $table (`$value_col`,`entity_id`) VALUES (:value, :entity_id)");
            $query->execute(['value' => $address_id, 'entity_id' => $entity]);
            return $con->lastInsertId();
        }
        return null;
    }

    /**
     * @inheritDoc
     */
    public function dataUpdate(int $entity): bool
    {
        $table = "field__".$this->field['field_name'];
        $value_col = "field__".$this->field['field_name'].'__value';
        $query = Database::database()->prepare("SELECT * FROM $table WHERE entity_id = :entity_id");
        $query->execute(['entity_id' => $entity]);
        $data =  $query->fetch(PDO::FETCH_ASSOC);
        $lid = $data[$value_col] ?? null;
        if($lid) {
            $fields = AddressFormat::fieldNames();
            // Swapping field keys
            $new_data = [];
            foreach($this->savable_data as $key => $value) {
                if(isset($fields[$key])) {
                    $field = $fields[$key];
                    $new_data[$field['storage_field_name']] = $value;
                }
            }
            $new_data['country_code'] = $this->savable_data['country'] ?? null;
            if(!empty($new_data['country_code'])) {
                $this->savable_data = $new_data;
            }

            // Query line building.
            $placeholders = array_map(function ($field) {
                return "$field = :$field";
            },array_keys($this->savable_data));

            $con = Database::database();
            $query_line = "UPDATE address_fields_data SET  ".implode(', ', $placeholders)." WHERE lid = :lid";
            $query = $con->prepare($query_line);
            foreach ($this->savable_data as $key => $value) {
                $query->bindValue(":$key", $value);
            }
            $query->bindValue(":lid", $lid);
            return $query->execute();
        }
        else {
            return !empty($this->dataSave($entity));
        }
    }

    /**
     * @inheritDoc
     */
    public function dataDelete(int $entity): bool
    {
        $table = "field__".$this->field['field_name'];
        $value_col = "field__".$this->field['field_name'].'__value';
        $query = Database::database()->prepare("SELECT * FROM $table WHERE entity_id = :entity_id");
        $query->execute(['entity_id' => $entity]);
        $data =  $query->fetch(PDO::FETCH_ASSOC);
        $lid = $data[$value_col] ?? null;
        if($lid) {
            $query = Database::database()->prepare("DELETE FROM address_fields_data WHERE lid = :lid");
            $query->execute(['lid' => $lid]);

            $query = Database::database()->prepare("DELETE FROM $table WHERE entity_id = :id");
            return $query->execute(['id' => $entity]);

        }
        return false;
    }

    /**
     * @inheritDoc
     */
    public function fetchData(int $entity): array
    {
        $table = "field__".$this->field['field_name'];
        $value_col = "field__".$this->field['field_name'].'__value';
        $query = Database::database()->prepare("SELECT * FROM $table WHERE entity_id = :entity_id");
        $query->execute(['entity_id' => $entity]);
        $data =  $query->fetch(PDO::FETCH_ASSOC);
        $lid = $data[$value_col] ?? null;
        $fields = AddressFormat::fieldNames();
        $new_data = array_map(function ($field) {
            return $field['storage_field_name'];
        }, $fields);
        $temp = [];
        foreach ($new_data as $key => $value) {
           $temp[$value] = null;
        }
        $new_data = $temp;
        if($lid) {
            $query = Database::database()->prepare("SELECT * FROM address_fields_data WHERE lid = :lid");
            $query->execute(['lid' => $lid]);
            $address =  $query->fetch(PDO::FETCH_ASSOC);
            if(!empty($address['lid'])) {
                unset($address['lid']);
            }
            return $address ?? $new_data;
        }
        return $new_data;
    }

    /**
     * @inheritDoc
     */
    public function setData(mixed $data): FieldInterface
    {
       $this->savable_data = $data;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function displayType(): array
    {
        return [
            [
                'label' => 'Full detailed address',
                'name' => 'full_address',
            ],
            [
                'label' => 'Minor detailed Address',
                'name' => 'minor_address',
            ],
            [
                'label' => 'Map',
                'name' => 'map_address',
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
        $setting = [
            'label' => $this->field['field_label'],
            'label_visible' => $this->field['field_settings']['field_label_visible'] ?? false,
            'label_name' => $this->getName(),
        ];
        $displayType = $this->getDisplayType();
        $display_name = $displayType['name'];
        foreach ($field_value as $value) {
            $field_value = $this->constructAddress($display_name, $value['value']);
        }
        return Services::create('render')->render('address_field_display_markup.php',['value' => $field_value, 'setting' => $setting]);
    }

    private function constructAddress(string $type, $address): string|null
    {
        if(!empty($address['country_code'])) {
            $country = (new Country($address['country_code']));
            $state = null;
            if(!empty($address['state_code'])) {
                $state = (new State($address['country_code'], $address['state_code']));
            }
            if($type === 'map_address' && $country->getName()) {
                //https://www.google.com/maps/search/?api=1&query=Mohali+Tower,+India
                $query = "{$address['address_1']} {$state?->getName()} {$address['city_id']} {$country->getName()}";
                $link = "https://www.google.com/maps/search/?api=1&". http_build_query(['query' => $query]);
                return "<div><p><a href='{$link}' target='_blank'>{$query}</a></p></div>";
            }

            if($type === 'minor_address' && $country->getName()) {
                return "<div>
           <p><strong>Country:</strong> &nbsp; {$country->getName()}<br>
             <strong>State:</strong> &nbsp; {$state?->getName()}<br>
             <strong>City:</strong> &nbsp; {$address['city_id']}<br>
             <strong>address 1:</strong> &nbsp; {$address['address_1']}<br>
             <strong>Address 2:</strong> &nbsp; {$address['address_2']}<br>
             <strong>Postal Code:</strong> &nbsp; {$address['zip_code']}<br>
             <strong>County:</strong> &nbsp; {$address['county']}<br>
           </p>
</div>";
            }

            if($type === 'full_address' && $country->getName()) {
                $query = "{$address['address_1']} {$state?->getName()} {$address['city_id']} {$country->getName()}";
                $link = "https://www.google.com/maps/search/?api=1&". http_build_query(['query' => $query]);
                return "<div>
           <p><strong>Country:</strong> &nbsp; {$country->getName()}<br>
           <strong>Flag:</strong> &nbsp; {$country->getEmoji()}<br>
           <strong>Region:</strong> &nbsp; {$country->getRegion()}<br>
           <strong>Country Capital:</strong> &nbsp; {$country->getCapital()}<br>
           <strong>Country Currency:</strong> &nbsp; {$country->getCurrency()}<br>
           <strong>Country Latitude:</strong> &nbsp; {$country->getLatitude()}<br>
           <strong>Country Longitude:</strong> &nbsp; {$country->getLongitude()}<br>
           <strong>Country Code:</strong> &nbsp; {$address['country_code']}<br>
             <strong>State:</strong> &nbsp; {$state?->getName()}<br>
             <strong>City:</strong> &nbsp; {$address['city_id']}<br>
             <strong>address 1:</strong> &nbsp; {$address['address_1']}<br>
             <strong>Address 2:</strong> &nbsp; {$address['address_2']}<br>
             <strong>Postal Code:</strong> &nbsp; {$address['zip_code']}<br>
             <strong>County:</strong> &nbsp; {$address['county']}<br> 
           </p></div>". "<div><p><a href='{$link}' target='_blank'>{$query}</a></p></div>";
            }
        }
        return null;
    }
}