<?php

namespace Mini\Cms\default\Controllers;

use Mini\Cms\Controller\ContentType;
use Mini\Cms\Controller\ControllerInterface;
use Mini\Cms\Controller\Request;
use Mini\Cms\Controller\Response;
use Mini\Cms\Controller\StatusCode;
use Mini\Cms\Field;
use Mini\Cms\Fields\FieldInterface;
use Mini\Cms\Fields\FieldViewDisplay\FieldViewDisplayInterface;
use Mini\Cms\Services\Services;
use Symfony\Component\HttpFoundation\RedirectResponse;

class FieldEdit implements ControllerInterface
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
        $field_name = $this->request->get('field_name');
        if(empty($field_name)) {
            (new RedirectResponse('/structure/content-type'))->send();
        }

        if($this->request->isMethod(\Symfony\Component\HttpFoundation\Request::METHOD_POST)) {
            $field_label = $this->request->getPayload()->get('field_name');
            $field_description = $this->request->getPayload()->get('field_description');
            $field_display = $this->request->getPayload()->get('field_display');
            $field_label_visible = $this->request->getPayload()->get('field_label_visible');

            $field = Field::load($this->request->get('field_name'));
            if($field instanceof FieldInterface) {

                if(!empty($field_label)) {
                    $field->setLabel($field_label);
                }
                if(!empty($field_description)) {
                    $field->setDescription($field_description);
                }
                if(!empty($field_display)) {
                    $settings = $field->displayType();
                    $settings = array_filter($settings,function ($item)use($field_display) {
                        return $item['name'] === $field_display;
                    });
                    $field->setDisplayFormat(reset($settings));
                }
                $field->setLabelVisible(!empty($field_label_visible));
                if($field->update()) {
                    (new RedirectResponse('/structure/content-type/field/'.$field_name.'/view'))->send();
                }
            }
        }

        $field = Field::load($field_name);
        $displays = $field instanceof FieldViewDisplayInterface ? $field->displayType() : [];
        $this->response->setContentType(ContentType::TEXT_HTML)
            ->setStatusCode(StatusCode::OK)
            ->write(Services::create('render')->render('content_types_field_editing.php',['field' => $field, 'displays' => $displays]));
    }
}