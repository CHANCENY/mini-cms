<?php

namespace Mini\Cms\Fields;

use Mini\Cms\Fields\FieldViewDisplay\FieldViewDisplayInterface;
use Mini\Cms\StorageManager\Connector;

interface FieldInterface extends FieldViewDisplayInterface
{
    /**
     * Getting field type.
     * @return mixed
     */
    public function getType();

    /**
     * Getting field name.
     * @return mixed
     */
    public function getName();

    /**
     * Getting field label.
     * @return mixed
     */
    public function getLabel();

    /**
     * Setting field label.
     * @param $label
     * @return mixed
     */
    public function setLabel($label);

    /**
     * Getting field description.
     * @return mixed
     */
    public function getDescription();

    /**
     * Setting field description.
     * @param $description
     * @return mixed
     */
    public function setDescription($description);

    /**
     * Check if field is required.
     * @return mixed
     */
    public function isRequired();

    /**
     * Loading field instance by name.
     * @param string $field
     * @return FieldInterface
     */
    public function load(string $field): FieldInterface;

    /**
     * Giving field name this name cannot change once field is saved.
     * @param string $name
     * @return mixed
     */
    public function setName(string $name);

    /**
     * Saving the field instance.
     * @return mixed
     */
    public function save();

    /**
     * Setting entity id where field should be pointing to or related to.
     * @param int $entityID
     * @return mixed
     */
    public function setEntityID(int $entityID);

    /**
     * Collecting field information.
     * @return mixed
     */
    public function getFieldDefinition();

    /**
     * Updating field instance.
     * @return bool
     */
    public function update(): bool;

    /**
     * Deleting the field instance.
     * @return bool
     */
    public function delete(): bool;

    /**
     * Giving display format of field.
     * @param array $displayFormat
     * @return void
     */
    public function setDisplayFormat(array $displayFormat):void;

    /**
     * Setting for label visibility.
     * @param bool $visible
     * @return void
     */
    public function setLabelVisible(bool $visible):void;

    /**
     * True if label should be visible
     * @return bool
     */
    public function isLabelVisible(): bool;

    /**
     * Saving field data on Node.
     * @param int $entity Node id.
     * @return array|int|null
     */
    public function dataSave(int $entity): array|int|null;

    /**
     * Updating field data on node.
     * @param int $entity Node Id.
     * @return bool
     */
    public function dataUpdate(int $entity): bool;

    /**
     * Deleting field data on node.
     * @param int $entity Node id.
     * @return bool
     */
    public function dataDelete(int $entity): bool;

    /**
     * Fetch field data on instance.
     * @param int $entity Giving related node id.
     * @return array
     */
    public function fetchData(int $entity): array;

    /**
     * This is for setting data up then calling the data handling functions.
     * @param mixed $data Data to be saved under field storage.
     * @return FieldInterface
     */
    public function setData(mixed $data): FieldInterface;

}