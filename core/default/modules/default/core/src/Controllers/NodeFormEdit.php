<?php

namespace Mini\Cms\default\modules\default\core\src\Controllers;

use Mini\Cms\Controller\Request;
use Mini\Cms\Controller\Response;
use Mini\Cms\Entities\Node;
use Mini\Cms\Modules\Content\Node\NodeType;
use Mini\Cms\Modules\Content\Storage\FieldStorage;
use Mini\Cms\Modules\FormControllerBase\FormControllerInterface;
use Mini\Cms\Modules\FormControllerBase\FormState;

class NodeFormEdit implements FormControllerInterface
{

    protected NodeType $nodeType;

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
        // TODO: Implement writeBody() method.
    }

    /**
     * @inheritDoc
     */
    public function getFormId(): string
    {
        $node = Node::load($this->request->get('nid'));
        return 'node-form-'.clean_string($node->getNodeType()->getTypeName(),'_',replace_char: '-').'-edit';
    }

    /**
     * @inheritDoc
     */
    public function buildForm(array $form, FormState $formState): array
    {
        $node = Node::load($this->request->get('nid'));
        $fields = $node->getFields();
        $formState->set('title', $node->getTitle());
        $formState->set('published', $node->get('status'));
        foreach ($fields as $field) {
            $formState->set($field->getName(), $node->get($field->getName()));
        }
        $node->getNodeType()->getForm($form, $formState);
        return $form;
    }

    /**
     * @inheritDoc
     */
    public function validateForm(array &$form, FormState &$formState): void
    {
        $formState->setValidated(true);
    }

    /**
     * @inheritDoc
     */
    public function submitForm(array &$form, FormState $formState): void
    {
        $node = Node::load($this->request->get('nid'));
        $fields = $node->getFields();
        $status = $formState->get('published',0) ? 1 : 0;
        $node->set('status', $status);
        $node->set('title', $formState->get('title'));
        foreach ($fields as $field) {
            $storage = $field->getStorage();
            if($storage instanceof FieldStorage) {
                if($storage->isMultipleAllowed()) {
                    $i = 0;
                    $data = [];
                    while (true) {
                        $field_name = $field->getName().'____'.$i;
                        if(!empty($formState->get($field_name))) {
                            $d = $formState->get($field_name);
                            if(is_array($d)) {
                                $data = array_merge($data, $d);
                            }else {
                                $data[] = $d;
                            }
                        }
                        $i++;
                        if($i === 20) {
                            break;
                        }
                    }
                    $node->set($field->getName(), $data);
                }
                else {
                    $node->set($field->getName(), $formState->get($field->getName()));
                }
            }
        }
        $node->save();
        dump($node);
        exit;
    }

    public function getTemplate(): string
    {
        return "content_type_form_.php";
    }
}