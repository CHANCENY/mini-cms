<?php

/**
@route Mini\Cms\Web\Controllers\PersonCreationForm

*/
namespace Mini\Cms\Web\Controllers\PersonCreationForm;

use Mini\Cms\Modules\FormControllerBase\FormControllerInterface;

use Mini\Cms\Controller\Request;

use Mini\Cms\Controller\Response;

use Mini\Cms\Controller\StatusCode;

use Mini\Cms\Controller\ContentType;

use Mini\Cms\Modules\FormControllerBase\FormState;

class PersonCreationForm implements FormControllerInterface {
    public function __construct(private Request &$request, private Response &$response){}

    public function validateForm(array &$form, FormState &$formState): void{

    }

   public function getFormId(): string{ return "change_this_form_id"; }

   public function writeBody(): void{}

   public function isAccessAllowed(): bool{ return true; }

   public function submitForm(array &$form, FormState $formState): void{}

   public function buildForm(array $form, FormState $formState): array{
      $form["field_sample"] = [
            "#type" => "text",
            "#title" => "Sample field",
            "#required" => true,
            "#placeholder" => "enter sample data",
            "#attributes" => ["class" => "form-control", "id" => "sample-field"],
            "#description" => "Please enter your sample data.",
            "#default_value" => $formState->get("field_sample"),
        ];
      $form['section'] = [
          '#type' => 'details',
          '#title' => "Section",
          '#description' => "Person Information",
          '#attributes' => ["class" => "section"],

      ];
      $form['section']['first_name'] = [
          '#type' => 'text',
          '#title' => "FirstName 1",
          '#required' => true,
          '#placeholder' => "Enter first name",
          '#attributes' => ["class" => "form-control", "id" => "first-name-1"],
          '#description' => "Please enter your first name.",
          "#default_value" => $formState->get("first_name"),
      ];
      $form['submit'] = [
           '#type' => 'submit',
           '#value' => 'Submit',
           '#attributes' => ['class' => 'btn btn-primary'],
       ];
       return $form;
    }

}

