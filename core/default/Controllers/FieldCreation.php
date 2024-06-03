<?php

namespace Mini\Cms\default\Controllers;

use Mini\Cms\Controller\ContentType;
use Mini\Cms\Controller\ControllerInterface;
use Mini\Cms\Controller\Request;
use Mini\Cms\Controller\Response;
use Mini\Cms\Controller\StatusCode;
use Mini\Cms\Entity;
use Mini\Cms\Field;
use Mini\Cms\Fields\FieldInterface;
use Mini\Cms\Fields\FieldViewDisplay\FieldViewDisplayInterface;
use Mini\Cms\Services\Services;
use Symfony\Component\HttpFoundation\RedirectResponse;

class FieldCreation implements ControllerInterface
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
        $action = $this->request->get('action');
        $type = $this->request->get('type');
        if($action === 'display' && $type) {
            $field = Field::create($type);
            if($field instanceof FieldViewDisplayInterface) {
                $setting = $field->displayType();

                $this->response->setContentType(ContentType::APPLICATION_JSON)
                    ->setStatusCode(StatusCode::OK)
                    ->write($setting);
            }
            return;
        }
        $entity = Entity::load($this->request->get('content_type_name'));
        if($this->request->isMethod(\Symfony\Component\HttpFoundation\Request::METHOD_POST)) {
            $data = $this->request->getPayload();
            $field_type = $data->get('field_type');
            $field_name = $data->get('field_name');
            $field_description = $data->get('field_description');
            $field_required = $data->get('field_required');
            $field_display = $data->get('field_display');
            $field_label_visible = $data->get('field_label_visible');
            $field_size = $data->get('field_size');
            $field_default_value = $data->get('field_default_value');
            $reference_settings = [];
            if($field_type === 'reference') {
                $ref = $data->get('reference_setting');
                $refList = explode('|', $ref);
                $reference_settings = [
                    'reference_type' => $refList[0],
                    'reference_name' => $refList[1]
                ];
            }

            if(!empty($field_type) && !empty($field_name) && !empty($field_description) && !empty($field_size)) {
               $field = Field::create($field_type);
               if($field instanceof FieldInterface) {
                   $field->setLabel($field_name);
                   $field->setDescription($field_description);
                   $field->setRequired(!empty($field_required));
                   $field->setSize($field_size);
                   $field->setName($field_name);
                   $field->setDefaultValue($field_default_value);
                   $field->setEntityID($entity->entityId());
                   $settings = $field->displayType();
                   $settings = array_filter($settings,function ($item)use($field_display) {
                       return $item['name'] === $field_display;
                   });
                   $field->setDisplayFormat(reset($settings));
                   $field->setLabelVisible(!empty($field_label_visible));
                   if(!empty($reference_settings)) {
                       $field->setReferenceSettings($reference_settings);
                   }
                   if($field->save()) {
                       (new RedirectResponse('/structure/content-type/'.$entity->getEntityTypeName(). '/fields'))->send();
                   }
               }
            }
        }
        $fields = Field::fields();
        $this->response->setContentType(ContentType::TEXT_HTML)
            ->setStatusCode(StatusCode::OK)
            ->write(Services::create('render')->render('content_type_field_creation_form.php', ['fields'=>$fields]));
    }
}