<?php

namespace Mini\Cms\Fields\MarkUp;

use Mini\Cms\Fields\FieldInterface;
use Mini\Cms\Fields\FieldMarkUpInterface;

class TextAreaFieldMarkUp implements FieldMarkUpInterface
{

      private string $markup;

    private FieldInterface $field;


    public function buildMarkup(FieldInterface $field, array|null $default_value): FieldMarkUpInterface
    {
        $default_value = $default_value['value'] ?? null;
        $this->field = $field;
        $display_setting = $field->getDisplayType()['name'] ?? 'plain_text';
        $class_name = match ($display_setting) {
            'plain_text' => null,
            'full_html' => 'tinymce-editor',
             default => 'quill-editor',
        };

        $is_required = !empty($field->isRequired()) ? 'required' : null;
        $is_required = $display_setting === 'full_html' ? null : $is_required;
        $size = $this->field->getSize();
        $this->markup = <<<FIELD_MARKUP
               <div class="form-group field-markup mt-3">
               <label for="field-{$this->field->getName()}">{$this->field->getLabel()}</label>
               <textarea name="{$this->field->getName()}" id="field-{$this->field->getName()}" class="form-control input-field-text $class_name"
                $is_required size="$size">{$default_value}</textarea>
               </div>
FIELD_MARKUP;
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