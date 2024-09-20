<?php

namespace Mini\Cms\Web\Controllers;

use Mini\Cms\Controller\ContentType;
use Mini\Cms\Controller\Request;
use Mini\Cms\Controller\Response;
use Mini\Cms\Controller\StatusCode;
use Mini\Cms\Modules\FormControllerBase\FormControllerInterface;
use Mini\Cms\Modules\FormControllerBase\FormState;

class Sample implements FormControllerInterface
{

    public function __construct(private Request &$request, private Response &$response)
    {
    }

    /**
     * @inheritDoc
     */
    public function isAccessAllowed(): bool
    {
        return true;
    }

    public function writeBody(): void
    {
        $this->response->setStatusCode(StatusCode::OK)
            ->setContentType(ContentType::TEXT_HTML)
            ->write("<h1>Hello World!</h1>");
    }

    public function getFormId(): string{ return "sample_form_form"; }

    public function validateForm(array &$form, FormState &$formState): void{}

    public function submitForm(array &$form, FormState $formState): void
    {
        dump($form, $formState->get('profile_image')[0]->id());
    }

    public function buildForm(array $form, FormState $formState): array
    {
        return [
            "field_sample" => [
                "#type" => "text",
                "#title" => "Sample field",
                "#required" => true,
                "#placeholder" => "enter sample data",
                "#attributes" => ["class" => "form-control", "id" => "sample-field"],
                "#description" => "Please enter your sample data.",
                "#default_value" => $formState->get("field_sample"),
            ],
            'profile_image' => [
                '#type' => 'file',
                '#title' => 'Profile Image',
                '#required' => true,
                '#placeholder' => 'Profile',
                '#attributes' => ['class' => 'form-control', 'id' => 'profile_image', 'multiple'=>'multiple'],
                '#description' => 'Please enter your profile image.',
                '#default_value' => $formState->get('profile_image'),
            ],
            'submit' => [
                '#type' => 'submit',
                '#value' => 'Submit',
                '#attributes' => ['class' => 'btn btn-primary'],
            ],
            'reset' => [
                '#type' => 'reset',
                '#value' => 'Reset',
                '#attributes' => ['class' => 'btn btn-secondary'],
            ],
        ];
    }

}