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

}