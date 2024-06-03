<?php

namespace Mini\Cms\Fields\MarkUp;

use Mini\Cms\Fields\FieldInterface;
use Mini\Cms\Fields\FieldMarkUpInterface;

class EmailFieldMarkUp implements FieldMarkUpInterface
{

    private string $markup;

    private FieldInterface $field;

    /**
     * @inheritDoc
     */
    public function buildMarkup(FieldInterface $field, ?array $default_value): FieldMarkUpInterface
    {
        $default_value = $default_value['value'] ?? null;
        $this->field = $field;
        $is_required = !empty($field->isRequired()) ? 'required' : null;
        $size = $this->field->getSize();
        $this->markup = <<<FIELD_MARKUP
               <div class="form-group field-markup mt-3">
               <label for="field-{$this->field->getName()}">{$this->field->getLabel()}</label>
               <input type="email" name="{$this->field->getName()}" id="field-{$this->field->getName()}" class="form-control input-field-text"
                $is_required size="$size" value="{$default_value}">
               </div>
FIELD_MARKUP;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getMarkup(): string
    {
        return $this->markup;
    }

    /**
     * @inheritDoc
     */
    public function setMarkup(string $markup): FieldMarkUpInterface
    {
        $this->markup = $markup;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getField(): FieldInterface
    {
        return $this->field;
    }

    public function __toString()
    {
        return $this->markup;
    }
}