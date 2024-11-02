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
        $this->inputFields = array_merge($_POST,$_FILES);
        $request = Request::createFromGlobals();
        $this->redirect_url = $request->headers->get('referer', '/');
        $this->values = $request->request->all();
    }

    public function setValidated(bool $validated): void
    {
        $this->validated = $validated;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function isValidated(): bool
    {
        return $this->validated;
    }

    public function set(string $key, $value): void
    {
        $this->values[$key] = $value;
    }

    public function get(string $string): mixed
    {
        return $this->values[$string] ?? null;
    }

    public function getRaw(string $string): mixed
    {
        return $this->inputFields[trim($string)] ?? null;
    }

    public function setErrors(string $field_name, string|MarkUp $error_message): void
    {
        $this->errors[$field_name] = $error_message;
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