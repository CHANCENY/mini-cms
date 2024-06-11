<?php

namespace Mini\Cms\Fields;

use Mini\Cms\Connections\Database\Database;
use Mini\Cms\Fields\FieldInterface;
use Mini\Cms\Services\Services;
use Mini\Cms\StorageManager\FieldRequirementNotFulFilledException;
use PDO;

class EmailField implements FieldInterface
{

    private array $field = [];
    private mixed $savable_data;

    public function __construct()
    {
        $this->field = [
            'field_type' => 'email',
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
        return $this->field['type'] ?? 'email';
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
    public function setEntityID(int $entityID): void
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

    /**
     * @inheritDoc
     */
    public function dataUpdate(int $entity): bool
    {
        $table = "field__".$this->field['field_name'];
        $value_col = "field__".$this->field['field_name'].'__value';

        if(empty($this->savable_data)) {
            $this->dataDelete($entity);
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

    /**
     * @inheritDoc
     */
    public function dataDelete(int $entity): bool
    {
        $table = "field__".$this->field['field_name'];
        $query = "DELETE FROM $table WHERE entity_id = :entity_id";
        $statement = Database::database()->prepare($query);
        return $statement->execute(['entity_id' => $entity]);
    }

    /**
     * @inheritDoc
     */
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

    /**
     * @inheritDoc
     */
    public function setData(mixed $data): FieldInterface
    {
        if(is_array($data)) {
            if($this->getSize()  < 1) {
                throw new \Exception("Field accept only one value");
            }
        }
        if($this->isRequired() && !is_numeric($data) && empty($data)) {
            if(!$this->getDefaultValue()) {
                throw new \Exception("Field cannot be empty");
            }
            $data = $this->getDefaultValue();
        }
        if(is_string($data) && !filter_var($data, FILTER_VALIDATE_EMAIL)) {
            throw new \Exception("Field cannot be a valid email address");
        }
        if(is_array($data)) {
            foreach($data as $value) {
                if(!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    throw new \Exception("Field cannot be a valid email address");
                }
            }
        }
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
                'label' => 'Text',
                'name' => 'text',
            ],
            [
                'label' => 'Mail Link',
                'name' => 'mail_to',
            ],
            [
                'label' => 'On image',
                'name' => 'on_image'
            ]
        ];
    }

    /**
     * @inheritDoc
     */
    public function getDisplayType(): array
    {
        return $this->field['field_settings']['field_display_type'] ?? [
            'label' => 'Text',
            'name' => 'text',
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
            if($display_name === 'text') {
                $field_value = trim($value['value']);
            }
            if($display_name === 'mail_to') {
                $field_value = "<a href='mailto:".$value['value']."' title='send mail to ".$field_value['value']."'>".$value['value']."</a>";
            }
            if($display_name === 'on_image') {
                $field_value = "<img src='".$this->emailToImage(trim($value['value']))."' alt='image of email' />";
            }
        }
        return Services::create('render')->render('email_field_display_markup.php',['value' => $field_value, 'setting' => $setting]);
    }

    private function emailToImage(string $text): string
    {
        // Define font size, angle, and path to the TTF font file
        $fontSize = 20; // replace fontsize with a numerical value
        $angle = 0;
        $fontPath = __DIR__ . '/assets/Arial-Unicode-Regular.ttf';

        // Calculate the bounding box of the text
        $bbox = imagettfbbox($fontSize, $angle, $fontPath, $text);

        // Calculate the width and height of the image based on the bounding box
        $width = abs($bbox[4] - $bbox[0]) + 20; // adding some padding
        $height = abs($bbox[5] - $bbox[1]) + 20; // adding some padding

        // Create the image
        $image = imagecreate($width, $height);

        // Set the background color (white)
        $bg = imagecolorallocatealpha($image, 255, 255, 255, 0);

        // Set the text color (black)
        $black = imagecolorallocate($image, 0, 0, 0);

        // Calculate the x and y coordinates to center the text
        $x = 10; // padding from the left
        $y = $height - 10; // padding from the bottom

        // Add the text to the image
        imagettftext($image, $fontSize, $angle, $x, $y, $black, $fontPath, $text);

        // Define the path where the image will be saved
        $imagePath = 'public://email_field';
        if(!is_dir($imagePath)) {
            mkdir($imagePath);
        }
        $imagePath .= '/'.time().'.png';

        // Output the image as a PNG file to the specified path
        imagepng($image, $imagePath);

        // Free up memory
        imagedestroy($image);
        $base = base64_encode(file_get_contents($imagePath));
        unlink($imagePath);
        return 'data:image/png;base64,'. $base;
    }
}