<?php

namespace Mini\Cms\Fields\MarkUp;

use Mini\Cms\Fields\FieldInterface;
use Mini\Cms\Fields\FieldMarkUpInterface;
use Mini\Cms\Modules\Respositories\Territory\AddressFormat;

class AddressFieldMarkUp implements FieldMarkUpInterface
{

    private string $markup;

    private FieldInterface $field;

    public function buildMarkup(FieldInterface $field, array|null $default_value): FieldMarkUpInterface
    {
        $address = new AddressFormat();
        $default_value = $default_value['value'] ?? null;
        $this->field = $field;
        $is_required = !empty($field->isRequired()) ? 'required' : null;
        $size = $this->field->getSize();
        $full_field = "<input type='hidden' name='{$field->getName()}' class='field-field-address-field'>";
        $this->markup = $full_field . $address->getAddressMarkUp($field->getName(),$default_value ?? 'US');
        return $this;
    }

    public function getMarkup(): string
    {
        return $this->markup;
    }

    public function setMarkup(string $markup): FieldMarkUpInterface
    {
        $this->markup = $markup;
        return $this;
    }

    public function getField(): FieldInterface
    {
        return $this->field;
    }

    public function __toString()
    {
        return $this->markup;
    }
}