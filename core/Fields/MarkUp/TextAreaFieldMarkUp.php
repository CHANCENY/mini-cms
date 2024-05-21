<?php

namespace Mini\Cms\Fields\MarkUp;

use Mini\Cms\Fields\FieldInterface;
use Mini\Cms\Fields\FieldMarkUpInterface;

class TextAreaFieldMarkUp implements FieldMarkUpInterface
{

      private string $markup;

    private FieldInterface $field;


    public function buildMarkup(FieldInterface $field): FieldMarkUpInterface
    {
       $this->field = $field;
        $is_required = !empty($field->isRequired()) ? 'required' : null;
        $size = $this->field->getSize();
        $this->markup = <<<FIELD_MARKUP
               <div class="form-group field-markup mt-3">
               <label for="field-{$this->field->getName()}">{$this->field->getLabel()}</label>
               <textarea name="{$this->field->getName()}" id="field-{$this->field->getName()}" class="form-control input-field-text"
                $is_required size="$size"></textarea>
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