<?php

namespace Mini\Cms\Fields;

use Mini\Cms\StorageManager\Connector;

interface FieldInterface
{
    public function getType();

    public function getName();


    public function getLabel();

    public function setLabel($label);

    public function getDescription();

    public function setDescription($description);

    public function isRequired();

    public function load(string $field): FieldInterface;

    public function setName(string $name);

    public function save();

    public function setEntityID(int $entityID);

    public function getFieldDefinition();

    public function update(): bool;

    public function delete(): bool;

    public function setDisplayFormat(array $displayFormat):void;

    public function setLabelVisible(bool $visible):void;

    public function isLabelVisible(): bool;

    public function dataSave(int $entity): array|int|null;

    public function dataUpdate(int $entity): bool;

    public function dataDelete(int $entity): bool;

    public function fetchData(int $entity): array;

    public function setData(mixed $data): FieldInterface;

}