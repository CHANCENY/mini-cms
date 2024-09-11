<?php

namespace Mini\Cms\Modules\FormControllerBase;

use Mini\Cms\Controller\ControllerInterface;

interface FormControllerInterface extends ControllerInterface
{

    /**
     * Form id
     * @return string
     */
    public function getFormId(): string;

    /**
     * Building form array.
     * @param array $form
     * @param FormState $formState
     * @return array
     */
    public function buildForm(array $form, FormState $formState): array;

    /**
     * Validate form.
     * @param array $form
     * @param FormState $formState
     * @return void
     */
    public function validateForm(array &$form, FormState &$formState): void;

    /**
     * Submit handler.
     * @param array $form
     * @param FormState $formState
     * @return void
     */
    public function submitForm(array &$form, FormState $formState): void;

}