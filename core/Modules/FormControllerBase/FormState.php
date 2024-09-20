<?php

namespace Mini\Cms\Modules\FormControllerBase;

use Mini\Cms\Modules\FileSystem\File;
use Mini\Cms\Theme\MarkUp;
use Symfony\Component\HttpFoundation\Request;

class FormState
{
    public string $redirect_url;
    private array $errors;

    private bool $validated;

    private array $inputFields;
    
    private array $fields;

    private array $values;

    public function getInputFields(): array
    {
        return $this->inputFields;
    }

    public function getFields(): array
    {
        return $this->fields;
    }

    public function getValues(): array
    {
        return $this->values;
    }

    public function getFormSettings(): array
    {
        return $this->formSettings;
    }

    private array $formSettings;

    public function __construct(array $form_settings, bool $is_submitted)
    {
        $this->fields = array_keys($_POST);
        $this->validated = false;
        $this->errors = [];
        $this->values = [];
        $this->formSettings = $form_settings;
        $this->inputFields = $_POST;
        if ($is_submitted) {
            $this->defaultValidation();
            $this->processSubmitted();
        }
        $this->redirect_url = Request::createFromGlobals()->headers->get('referer');
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function isValidated(): bool
    {
        return $this->validated;
    }

    private function defaultValidation(): void
    {
        foreach ($this->formSettings as $key => $value) {
            if(in_array($key, $this->fields)) {
                if(isset($value['#required']) && $value['#required'] && $this->get($key) !== null) {
                  $this->values[$key] = $this->get($key);
                }
                elseif (isset($value['#required']) && $value['#required'] && $this->get($key) === null) {
                    $this->setErrors($key, "{$value['#title']} field is required.");
                }
                if($this->get($key) !== null) {
                    $this->values[$key] = $this->get($key);
                }
                if(empty($this->errors)) {
                    $this->validated = true;
                }
            }
        }
    }

    public function get(string $string): mixed
    {
        return $this->values[$string] ?? $this->inputFields[$string] ?? null;
    }

    public function setErrors(string $field_name, string|MarkUp $error_message): void
    {
        $this->errors[$field_name] = $error_message;
    }

    private function processSubmitted(): void
    {
        foreach ($this->values as $key => $value) {
            $value_o = $this->formSettings[$key];
            if(isset($value_o['#type']) && $value_o['#type'] === 'file') {
                $list = explode(',', $this->get($key));
                $files = array_map(function ($el) {
                    return File::load($el);
                },$list);
                $this->values[$key] = $files;
            }
        }
    }

    public function getRedirectUrl(): string
    {
        return $this->redirect_url;
    }

    public function setRedirectUrl(string $redirect_url): void
    {
        $this->redirect_url = $redirect_url;
    }

    public function cache(): int
    {
        return 0;
    }

}