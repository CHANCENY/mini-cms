<?php

namespace Mini\Cms\Modules\FormControllerBase;

class FormBuilder {
    protected $formArray;

    protected $dom;

    public function __construct($formArray) {
        $this->formArray = $formArray;
        $this->dom = new \DOMDocument('1.0', 'UTF-8');
    }

    public function buildForm(): false|string
    {
        $form = $this->dom->createElement('form');
        $form->setAttribute('method', 'post');

        foreach ($this->formArray as $name => $field) {
            $formElement = $this->createElement($name, $field);
            if ($formElement) {
                $form->appendChild($formElement);
            }
        }

        $this->dom->appendChild($form);
        return $this->dom->saveHTML();
    }

    protected function createElement($name, $field): false|\DOMElement|null
    {

        switch ($field['#type']) {
            case 'checkbox':
            case 'color':
            case 'datetime-local':
            case 'image':
            case 'month':
            case 'radio':
            case 'range':
            case 'tel':
            case 'text':
            case 'time':
            case 'url':
            case 'date':
            case 'number':
            case 'password':
            case 'email':
            case 'week':
                return $this->createInputField($name, $field);
            case 'textarea':
                return $this->createTextarea($name, $field);
            case 'select':
                return $this->createSelect($name, $field);
            case 'checkboxes':
            case 'radios':
                return $this->createOptions($name, $field);
            case 'file':
                return $this->createFileInput($name, $field);
            case 'details':
                return $this->createDetails($name, $field);
            case 'hidden':
                return $this->createHiddenField($name, $field);
            case 'submit':
            case 'reset':
            case 'button':
                return $this->createButton($name, $field);
            default:
                return null;
        }
    }

    protected function createInputField($name, $field): false|\DOMElement
    {
        $input = $this->dom->createElement('input');
        $input->setAttribute('type', $field['#type']);
        $input->setAttribute('name', $name);
        $input->setAttribute('placeholder', $field['#placeholder'] ?? '');
        $input->setAttribute('value', $field['#default_value'] ?? '');

        $this->setAttributes($input, $field['#attributes'] ?? []);
        return $this->wrapWithLabel($name, $field, $input);
    }

    protected function createTextarea($name, $field): false|\DOMElement
    {
        $textarea = $this->dom->createElement('textarea', $field['#default_value'] ?? '');
        $textarea->setAttribute('name', $name);
        $textarea->setAttribute('placeholder', $field['#placeholder'] ?? '');
        $this->setAttributes($textarea, $field['#attributes'] ?? []);
        return $this->wrapWithLabel($name, $field, $textarea);
    }

    protected function createSelect($name, $field): false|\DOMElement
    {
        $select = $this->dom->createElement('select');
        $select->setAttribute('name', $name);

        $this->setAttributes($select, $field['#attributes'] ?? []);

        foreach ($field['#options'] as $value => $label) {
            $option = $this->dom->createElement('option', $label);
            $option->setAttribute('value', $value);
            if ($value == $field['#default_value'] ?? '') {
                $option->setAttribute('selected', 'selected');
            }
            $select->appendChild($option);
        }

        return $this->wrapWithLabel($name, $field, $select);
    }

    protected function createOptions($name, $field): false|\DOMElement
    {
        $container = $this->dom->createElement('div');

        foreach ($field['#options'] as $value => $label) {
            $input = $this->dom->createElement('input');
            $input->setAttribute('type', $field['#type'] === 'checkboxes' ? 'checkbox' : 'radio');
            $input->setAttribute('name', $name . ($field['#type'] === 'checkboxes' ? '[]' : ''));
            $input->setAttribute('value', $value);
            if (in_array($value, (array) $field['#default_value'] ?? '')) {
                $input->setAttribute('checked', 'checked');
            }
            $input->setAttribute('id', $name . '_' . $value);

            $labelElement = $this->dom->createElement('label', $label);
            $labelElement->setAttribute('for', $name . '_' . $value);

            $container->appendChild($input);
            $container->appendChild($labelElement);
        }

        return $this->wrapWithLabel($name, $field, $container, false);
    }

    protected function createFileInput($name, $field): false|\DOMElement
    {
        $input = $this->dom->createElement('input');
        $input->setAttribute('type', 'file');
        $input->setAttribute('name', $name);

        $this->setAttributes($input, $field['#attributes'] ?? []);
        return $this->wrapWithLabel($name, $field, $input);
    }

    protected function createDetails($name, $field): false|\DOMElement
    {
        $details = $this->dom->createElement('details');
        if (!empty($field['#open'])) {
            $details->setAttribute('open', 'open');
        }

        $summary = $this->dom->createElement('summary', $field['#title']);
        $details->appendChild($summary);

        foreach ($field as $childName => $childField) {
            if (strpos($childName, '#') !== 0) {
                $childElement = $this->createElement($childName, $childField);
                if ($childElement) {
                    $details->appendChild($childElement);
                }
            }
        }

        return $details;
    }

    protected function wrapWithLabel($name, $field, $element, $includeLabel = true): false|\DOMElement
    {
        $container = $this->dom->createElement('div');

        if ($includeLabel) {
            $label = $this->dom->createElement('label', $field['#title']);
            $label->setAttribute('for', $name);
            $container->appendChild($label);
        }

        $container->appendChild($element);

        if (!empty($field['#description'])) {
            $description = $this->dom->createElement('small', $field['#description']);
            $description->setAttribute('class', 'form-text text-muted');
            $container->appendChild($description);
        }

        return $container;
    }

    protected function setAttributes($element, $attributes): void
    {
        foreach ($attributes as $attr => $value) {
            $element->setAttribute($attr, $value);
        }
    }

    protected function createHiddenField($name, $field): false|\DOMElement
    {
        $input = $this->dom->createElement('input');
        $input->setAttribute('type', 'hidden');
        $input->setAttribute('name', $name);
        $input->setAttribute('value', $field['#default_value'] ?? '');

        $this->setAttributes($input, $field['#attributes'] ?? []);
        return $input;
    }

    protected function createButton($name, $field): false|\DOMElement
    {
        $button = $this->dom->createElement('button', $field['#value']);
        $button->setAttribute('type', $field['#type']);
        $button->setAttribute('name', $name);

        $this->setAttributes($button, $field['#attributes'] ?? []);
        return $button;
    }
}