<?php

namespace Mini\Cms\Modules\FormControllerBase;

use Mini\Cms\Mini;

abstract class FormBase
{
    /**
     * Get form id
     * @return string
     */
    public function getFormId():string
    {
        return "";
    }

    /**
     * Building form array.
     * @param array $form
     * @param FormState $formState
     * @return array
     */
    public function buildForm(array &$form, FormState $formState): array
    {
        $form['form_id'] = array(
            '#type' => 'hidden',
            '#label' => 'Form Id',
            '#required' => true,
            '#placeholder' => 'Form id',
            '#attributes' => array('class' => 'form-control', 'id' => 'form-id'),
            '#description' => 'Form Id',
            '#default_value' => $this->getFormId(),
        );
        return $form;
    }

    /**
     * Validate values
     * @param array $form
     * @param FormState $formState
     * @return void
     */
    public function validateForm(array &$form, FormState $formState): void
    {
        if($formState->isValidated()) {
           foreach ($formState->getErrors() as $error) {
               Mini::messenger()->addErrorMessage($error->getMessage());
           }
        }
    }

    /**
     * Handling submission.
     * @param array $form
     * @param FormState $formState
     * @return void
     */
    public function submitForm(array &$form, FormState $formState): void
    {

    }
}