<?php

namespace Mini\Cms\Modules\Form;

use Mini\Cms\Entity;
use Mini\Cms\Field;
use Mini\Cms\Fields\FieldInterface;
use Mini\Cms\Fields\FieldMarkUpInterface;
use Mini\Cms\Modules\Storage\Tempstore;
use Ramsey\Uuid\Uuid;

class FormBase
{

    private string $form_html;

    private array $fields_registered;

    public function getFieldsRegistered(): array
    {
        return $this->fields_registered;
    }

    public function buildForm(string $content_type_name): FormBase
    {
        $entity = Entity::load($content_type_name);
        $fields = $entity->getEntityFields();

        $this->form_html = <<<TILTLE
          <div class="form-group form-default-title">
          <label for="title">Title</label>
          <input type="text" name="title" class="form-control title-field" id="title">
</div>
TILTLE;

        $this->fields_registered[] = 'title';

        if($fields) {
            foreach ($fields as $key=>$field) {
                if($field instanceof FieldInterface) {
                    $markup = Field::markUp($field->getType());
                    if($markup instanceof FieldMarkUpInterface) {
                        $this->form_html .= $markup->buildMarkup($field)->getMarkup(). PHP_EOL;
                        $this->fields_registered[] = $field->getName();
                    }
                }
            }
        }

       $form_id = Uuid::uuid4()->toString();
        $this->form_html .= <<<TILTLE
          <div class="form-group form-default-title">
          <input type="hidden" name="form_id" value="$form_id" class="form-control title-field" id="form_id">
</div>
TILTLE;

        Tempstore::save($form_id,$this);
        return $this;
    }

    public function getFormHtml(): string
    {
        return $this->form_html;
    }
}