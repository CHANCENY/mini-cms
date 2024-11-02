<?php

namespace Mini\Cms\default\modules\default\core\src\Controllers;

use Mini\Cms\Controller\Request;
use Mini\Cms\Controller\Response;
use Mini\Cms\Entities\Node;
use Mini\Cms\Modules\Content\Node\NodeType;
use Mini\Cms\Modules\FormControllerBase\FormControllerInterface;
use Mini\Cms\Modules\FormControllerBase\FormState;

class NodeForm implements FormControllerInterface
{

    protected NodeType $nodeType;
    public function __construct(private Request &$request, private Response &$response)
    {
        $this->nodeType = new NodeType($this->request->get('type'));
    }

    public function isAccessAllowed(): bool
    {
        return true;
    }

    public function writeBody(): void
    {
        // TODO: Implement writeBody() method.
    }

    public function getFormId(): string
    {
        return 'node-form-'.clean_string($this->nodeType->getTypeName(),'_',replace_char: '-');
    }

    public function buildForm(array $form, FormState $formState): array
    {
        $this->nodeType->getForm($form, $formState);
        return $form;
    }

    public function validateForm(array &$form, FormState &$formState): void
    {
        $formState->setValidated(true);
    }

    public function submitForm(array &$form, FormState $formState): void
    {
        $node = Node::create($this->request->get('type'),$formState->getValues());
        $node->save();

    }

    public function getTemplate(): string
    {
        return "content_type_form_.php";
    }

    public function editForm(): void
    {
    }
}