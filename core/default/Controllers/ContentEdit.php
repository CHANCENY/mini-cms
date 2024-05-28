<?php

namespace Mini\Cms\default\Controllers;

use Mini\Cms\Controller\ContentType;
use Mini\Cms\Controller\ControllerInterface;
use Mini\Cms\Controller\Request;
use Mini\Cms\Controller\Response;
use Mini\Cms\Controller\StatusCode;
use Mini\Cms\Entities\Node;
use Mini\Cms\Entity;
use Mini\Cms\Field;
use Mini\Cms\Fields\FieldInterface;
use Mini\Cms\Modules\Form\FormBase;
use Mini\Cms\Modules\Storage\Tempstore;
use Mini\Cms\Services\Services;

class ContentEdit implements ControllerInterface
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
        $form = new FormBase();
        $node = Node::load((int) $this->request->get('node'));
        if($node instanceof Node) {
            $entity = Entity::load($node->type());
            if($this->request->isMethod(\Symfony\Component\HttpFoundation\Request::METHOD_POST)) {
                $payload = $this->request->getPayload();
                $form_id = $payload->get('form_id');
                if(!empty($form_id)) {
                    $fields = Tempstore::load($form_id);
                    if($fields instanceof FormBase) {
                        $fields_registered = $fields->getFieldsRegistered();
                        $payload2 = $payload->all();
                        $data = [];
                        foreach ($fields_registered as $key=>$item) {
                            if(gettype($payload2[$item]) === 'string') {
                                $data[$item] = htmlspecialchars($payload2[$item]);
                            }
                            if(gettype($payload2[$item]) === 'array') {
                                $data[$item] = $payload2[$item];
                            }
                        }

                        foreach ($data as $key=>$item) {
                            $field = Field::load($key);
                            if($field instanceof FieldInterface) {

                                // Lets handle file field if has multiple fids
                                if($field->getType() === 'file') {
                                    $fids = null;
                                    $size = $field->getSize();
                                    if($size > 1) {
                                        foreach ($item as $value) {
                                            $fids .= implode(',', explode(',', $value));
                                        }
                                        $data[$key] = array_map('intval', explode(',', $fids));
                                    }
                                    else {
                                        $fids = trim($item,',');
                                        $data[$key] = (int) trim($fids);
                                    }

                                }
                            }
                        }

                        if(!empty($data['title'])) {
                            $new_node = $node;
                            foreach ($data as $key=>$value) {
                               $new_node->set($key, $value);
                            }
                            $new_node->update();
                        }
                    }
                }

            }

            $fieldsForm = $form->buildForm($node->type(), $node)->getFormHtml();
            $this->response->setContentType(ContentType::TEXT_HTML)
                ->setStatusCode(StatusCode::OK)
                ->write(Services::create('render')->render('content_form.php', ['fields'=>$fieldsForm,'entity'=>$entity]));
        }
    }
}