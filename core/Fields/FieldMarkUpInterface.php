<?php

namespace Mini\Cms\Fields;

interface FieldMarkUpInterface
{
    public function buildMarkup(FieldInterface $field, array|null $default_value): self;

    public function getMarkup(): string;

    public function setMarkup(string $markup): self;

    public function getField(): FieldInterface;

}