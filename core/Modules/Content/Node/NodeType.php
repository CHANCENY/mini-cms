<?php

namespace Mini\Cms\Modules\Content\Node;

use Mini\Cms\Mini;
use Mini\Cms\Modules\Content\Field\FieldType;
use Mini\Cms\Modules\Content\Field\FieldTypeInterface;
use Mini\Cms\Modules\Content\Storage\FieldStorage;
use Mini\Cms\Modules\Content\Tait\ActionTrait;
use Mini\Cms\Modules\FormControllerBase\FormState;
use Mini\Cms\System\System;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;

class NodeType implements NodeTypeInterface
{

    use ActionTrait;

    public function __construct(?string $content_type)
    {
        $this->CONTENT_TYPE = [];
        $this->prepare();
        if(!is_null($content_type)) {
            $content_file = 'private://configs/types/'.$content_type.'.yml';
            $this->CONTENT_TYPE = $this->read($content_file) ?? [];
        }
    }

    public function getLabel(): string
    {
        return $this->CONTENT_TYPE['#content_label'] ?? '';
    }

    public function getTypeName(): string
    {
        return $this->CONTENT_TYPE['#content_name'] ?? '';
    }

    public function getDescription(): string
    {
        return $this->CONTENT_TYPE['#content_description'] ?? '';
    }

    public static function create(array $data): ?NodeTypeInterface
    {
        try{
            if(isset($data['name']) && $data['label']) {
                $type = new static(null);
                $type->setName($data['name']);
                $type->setLabel($data['label']);
                if($data['fields']) {
                    foreach ($data['fields'] as $field) {
                        $type->setField($field);
                    }
                }
                return $type;
            }
        }catch (\Throwable) {}
        return null;
    }

    public function save(): bool
    {
        $content_file = "private://configs/types/".$this->CONTENT_TYPE['#content_name'].".yml";
        return $this->write($content_file, $this->CONTENT_TYPE);
    }

    public function getFields(): array
    {
        return array_map(function ($item){
            return new FieldType($item);
        },$this->CONTENT_TYPE['#fields'] ?? []);
    }

    public function getField(string $field_name): FieldTypeInterface
    {
        $field = array_filter($this->CONTENT_TYPE['#fields'] ?? [],function ($field) use ($field_name){
            return $field === $field_name;
        });
        if(!empty($field)) {
            return new FieldType(reset($field));
        }
        throw new FileNotFoundException("Field $field_name not found");
    }

    public function setName(string $name): void
    {
        $this->CONTENT_TYPE['#content_name'] = clean_string($name, replace_char:'_');
    }

    public function setLabel(string $label): void
    {
        $this->CONTENT_TYPE['#content_label'] = $label;
    }

    public function setDescription(string $description): void
    {
        $this->CONTENT_TYPE['#content_description'] = $description;
    }

    public function setField(string $field_name): void
    {
        $this->CONTENT_TYPE['#fields'][] = clean_string($field_name,replace_char:'_');
    }

    public function getContentTypes(): ?array
    {
        $list_content_types = scandir('private://configs/types');

        if($list_content_types) {
            $list_content_types = array_diff($list_content_types,['.','..']);
            foreach ($list_content_types as $key=>$content_type) {
                $content_l = explode('.', $content_type);
                $content_name = $content_l[0];
                $list_content_types[$key] = new NodeType(trim($content_name));
            }
        }
        return $list_content_types;
    }

    public function unsetField(string $field_name): bool
    {
        if(in_array($field_name,$this->CONTENT_TYPE['#fields'])){
            $index = array_search($field_name,$this->CONTENT_TYPE['#fields']);
            unset($this->CONTENT_TYPE['#fields'][$index]);
            $content_file = "private://configs/types/".$this->CONTENT_TYPE['#content_name'].".yml";
            return $this->overwrite($content_file, $this->CONTENT_TYPE);
        }
        return false;
    }

    public static function loadTypes(): ?array
    {
        return (new self(null))->getContentTypes();
    }

    public function update(): bool
    {
        $content_file = "private://configs/types/".$this->CONTENT_TYPE['#content_name'].".yml";
        return $this->overwrite($content_file, $this->CONTENT_TYPE);
    }

    public function delete(): bool
    {
        // TODO: delete fields and data in db.
        $fields = $this->getFields();
        foreach ($fields as $field) {
            if($field instanceof FieldTypeInterface) {
                $field->delete();
            }
        }
        $content_file = "private://configs/types/".$this->CONTENT_TYPE['#content_name'].".yml";
        return $this->remove($content_file);
    }

    public function getForm(array &$form, FormState $formState): array
    {
        $form["title"] = [
            "#type" => "text",
            "#title" => "Title",
            "#required" => true,
            "#placeholder" => "title",
            "#attributes" => ["class" => "form-control", "id" => "title-node-field"],
            "#description" => "Please enter node title",
            "#default_value" => $formState->get("title"),
        ];
        $has_multiple = null;
        $fields = $this->getFields();
        foreach ($fields as $field) {
            if($field instanceof FieldTypeInterface){
                $field_nme = $field->getName();
                $storage = $field->getStorage();
                if($storage->isMultipleAllowed()) {
                    $form[$field_nme.'_section'] = [
                        "#type" => "details",
                        "#title" => $field->getLabel(),
                        '#open' => true,
                    ];
                    if(is_array($formState->get($field_nme))) {
                        foreach ($formState->get($field_nme) as $key => $value) {
                            $form[$field_nme.'_section'][$field_nme."____{$key}[]"] = [
                                "#type" => $field->getType(),
                                "#title" => $field->getLabel(),
                                '#required' => $storage->isNullable(),
                                '#description' => 'enter '.strtolower($field->getLabel()),
                                "#default_value" => $value,
                                "#attributes" => ["class" => "form-control", "id" => clean_string($field_nme,'_','-'), 'multiple'=>$field->getType() === 'file' ? 'multiple' : null],
                            ];
                        }
                    }
                    elseif (empty($formState->get($field_nme))) {
                        $form[$field_nme.'_section'][$field_nme.'[]'] = [
                            "#type" => $field->getType(),
                            "#title" => $field->getLabel(),
                            '#required' => $storage->isNullable(),
                            '#description' => 'enter '.strtolower($field->getLabel()),
                            "#default_value" => $formState->get($field_nme),
                            "#attributes" => ["class" => "form-control", "id" => clean_string($field_nme,'_','-'), 'multiple'=>$field->getType() === 'file' ? 'multiple' : null],
                        ];
                    }
                    $has_multiple = true;
                }
                else {
                    $value = $formState->get($field_nme);
                    $form[$field_nme] = [
                        "#type" => $field->getType(),
                        "#title" => $field->getLabel(),
                        '#required' => $storage->isNullable(),
                        '#description' => 'enter '.strtolower($field->getLabel()),
                        "#default_value" => is_array($formState->get($field_nme)) ? reset($value) : $formState->get($field_nme),
                        "#attributes" => ["class" => "form-control", "id" => clean_string($field_nme,'_','-')],
                    ];
                }
            }
        }
        $form["published"] = [
            "#type" => "checkbox",
            "#title" => "Published",
            "#required" => true,
            "#placeholder" => "publish",
            "#attributes" => ["class" => "form-check", "id" => "publish-node-field"],
            "#description" => "Please enter node title",
            "#options" => ["1" => "publish"],
            '#default_value' => $formState->get("published"),
        ];
        $settings_data = [];
        foreach ($fields as $field) {
            if($field instanceof FieldTypeInterface){
                $settings = $field->getStorage();
                $settings_data[][clean_string($field->getName(),'_','-')] = [
                    'is_null' => $settings->isNullable(),
                    'is_multiple' => $settings->isMultipleAllowed(),
                    'limit' => $settings->getMultipleLimit(),
                    'type' => $field->getType(),
                ];
            }
        }
        $form["form_settings"] = [
            "#type" => "hidden",
            "#title" => "form settings",
            "#attributes" => ["class" => "d-none", "id" => "form-settings-node-field"],
            '#default_value' => json_encode($settings_data,JSON_PRETTY_PRINT),
        ];
        $form["submit"] = [
            "#type" => "submit",
            "#title" => "Submit",
            "#attributes" => ["class" => "btn btn-primary", "id" => "submit-node-field"],
            '#value' => "Submit and Save",
        ];

        if($has_multiple) {
            $theme = Mini::currentTheme();
            $system = new System();
            $path = $system->getAppRoot().'/core/default/themes/mini_cms/assets/js/form-settings.js';
            $theme->setAsset("footer", "<script type='text/javascript'>".file_get_contents($path)."</script>|ext-lib");
        }

        return $form;
    }
}