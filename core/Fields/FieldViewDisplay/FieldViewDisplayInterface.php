<?php

namespace Mini\Cms\Fields\FieldViewDisplay;

interface FieldViewDisplayInterface
{
    /**
     * Returns array of display types with key name and label.
     * @return array
     */
    public function displayType(): array;

    /**
     * Field display setting.
     * @return array
     */
    public function getDisplayType(): array;

    /**
     * Building html markup of field.
     * @param array $field_value This is the value of field from node eg ['value'=>...] or ['values'=>...]
     * @return string
     */
    public function markUp(array $field_value): string;
}