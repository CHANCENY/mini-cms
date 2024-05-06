<?php

namespace Mini\Cms\Fields;

use Chance\Entity\ConnectorInterface;
use Chance\Entity\StorageManager\Connector;

interface FieldInterface extends ConnectorInterface
{
    public function getType();

    public function getName();


    public function getLabel();

    public function setLabel($label);

    public function getDescription();

    public function setDescription($description);

    public function isRequired();

    public function load(string $field);

    public function setName(string $name);

    public function save();

    public function connector(Connector $connector);

    public function setEntityID(int $entityID);

    public function getFieldDefinition();

}