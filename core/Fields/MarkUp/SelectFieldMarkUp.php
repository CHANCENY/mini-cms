<?php

namespace Mini\Cms\Fields\MarkUp;

use Mini\Cms\Fields\FieldInterface;
use Mini\Cms\Fields\FieldMarkUpInterface;

class SelectFieldMarkUp implements FieldMarkUpInterface
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
        $multiple = $field->isMultipleValue() ? 'multiple' : null;
        $name = !empty($multiple) ? $field->getName().'[]' : $field->getName();

        $option_line = $field->getDefaultValue();
        $option_line_list = explode(',', $option_line);
        $options = null;
        foreach ($option_line_list as $option_line) {
            $list = explode('|', $option_line);
            $options .= "<option value='{$list[0]}'>{$list[1]}</option>".PHP_EOL;
        }
        $this->markup = <<<FIELD_MARKUP
               <div class="form-group field-markup mt-3">
               <label for="field-{$this->field->getName()}">{$this->field->getLabel()}</label>
               <select type="tel" name="{$name}" $multiple id="field-{$this->field->getName()}" class="form-control input-field-text"
                $is_required>$options</select>
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