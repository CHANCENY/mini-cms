<?php

namespace Mini\Cms\Fields;

interface FieldMarkUpInterface
{
    /**
     * Build field html presentation.
     * @param FieldInterface $field
     * @param array|null $default_value
     * @return self
     */
    public function buildMarkup(FieldInterface $field, array|null $default_value): self;

    /**
     * Getting html presentation of field.
     * @return string
     */
    public function getMarkup(): string;

    /**
     * Overriding field html presentation.
     * @param string $markup
     * @return self
     */
    public function setMarkup(string $markup): self;

    /**
     * Getting field instance.
     * @return FieldInterface
     */
    public function getField(): FieldInterface;

}