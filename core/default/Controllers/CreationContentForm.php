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
use Mini\Cms\Modules\Respositories\Territory\AddressFormat;
use Mini\Cms\Modules\Storage\Tempstore;
use Mini\Cms\Services\Services;

class CreationContentForm implements ControllerInterface
{

    public function __construct(private Request &$request, private Response &$response)
    {
    }

    public function isAccessAllowed(): bool
    {
        return true;
    }

    public function writeBody(): void
    {
        $form = new FormBase();
        $fieldsForm = $form->buildForm($this->request->get('content_type_name'))->getFormHtml();
        $entity = Entity::load($this->request->get('content_type_name'));

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
                        if(!empty($payload2[$item]) && gettype($payload2[$item]) === 'string') {
                            $data[$item] = htmlspecialchars($payload2[$item]);
                        }
                        elseif(!empty($payload2[$item]) && gettype($payload2[$item]) === 'array') {
                            $data[$item] = $payload2[$item];
                        }
                        elseif (!empty($payload2[$item.'___country'])) {
                            $data[$item] = array_merge(['country'=>$payload2[$item.'___country']], AddressFormat::filterAddressValues($payload2, $item));
                        }
                        else {
                            $data[$item] = null;
                        }

                    }
                    foreach ($data as $key=>$item) {
                        $field = Field::load($key);
                        if($field instanceof FieldInterface) {

                            // Let's handle file field if it has multiple fids
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

                        $new_node = Node::create($entity->getEntityTypeName());
                        foreach ($data as $key=>$value) {

                            if($key === 'publish') {
                                $value = $value == 'on' ? 'Yes' : 'No';
                                $key = 'status';
                            }
                            $new_node->set($key,$value);
                        }
                        $new_node->save();
                    }
                }
            }

        }

        $this->response->setContentType(ContentType::TEXT_HTML)
            ->setStatusCode(StatusCode::OK)
            ->write(Services::create('render')->render('content_form.php', ['fields'=>$fieldsForm,'entity'=>$entity]));
    }
}