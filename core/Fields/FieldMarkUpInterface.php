<?php

namespace Mini\Cms\Fields;

interface FieldMarkUpInterface
{
    public function buildMarkup(FieldInterface $field): self;

    public function getMarkup(): string;

    public function setMarkup(string $markup): self;

    public function getField(): FieldInterface;
}