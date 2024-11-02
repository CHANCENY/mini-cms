<?php

namespace Mini\Cms\default\modules\default\core\src\Controllers;

use Mini\Cms\Controller\ControllerInterface;
use Mini\Cms\Controller\Request;
use Mini\Cms\Controller\Response;
use Mini\Cms\Entities\Node;
use Mini\Cms\Mini;
use Mini\Cms\Modules\Content\Field\FieldType;
use Mini\Cms\Modules\Content\Field\FieldTypeEnum;
use Mini\Cms\Modules\Content\Node\NodeType;
use Mini\Cms\Modules\Content\Storage\FieldStorage;
use Mini\Cms\Modules\ErrorSystem;
use Mini\Cms\Services\Services;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ContentTypeForm implements ControllerInterface
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
        if($this->request->getMethod() === 'POST') {
            if($this->request->request->has('content_name') && $this->request->request->has('content_label')) {
                $nodeType = new NodeType(null);
                $nodeType->setName($this->request->request->get('content_name'));
                $nodeType->setLabel($this->request->request->get('content_label'));
                if($this->request->request->has('content_description')) {
                    $nodeType->setDescription($this->request->request->get('content_description'));
                }
                try{
                    if($nodeType->save()) {
                        Mini::messenger()->addSuccessMessage("Content Type Created You can add fields");
                    }
                }catch (\Throwable $e) {
                    Mini::messenger()->addErrorMessage($e->getMessage());
                }
                (new RedirectResponse($this->request->headers->get('referer')))->send();
                exit;
            }
        }
        $this->response->write(Services::create('render')->render('content_type_creation_form.php'));
    }

    public function contentTypeListing(): void
    {
        $types = NodeType::loadTypes();
        $this->response->write(Services::create('render')->render('content_type_listing.php',['content_types'=>$types]));
    }

    public function updateContentType(): void
    {
        $type = new NodeType($this->request->get('type'));
        if($this->request->getMethod() === 'POST') {
            $type->setLabel($this->request->request->get('content_label'));
            $type->setDescription($this->request->request->get('content_description'));
            if($type->update()) {
                Mini::messenger()->addSuccessMessage("Content Type Updated You can add fields");
                (new RedirectResponse($this->request->headers->get('referer')))->send();
                exit;
            }
        }
        $nids = Node::loadMultiple(bundle: $this->request->get('type'));
        $this->response->write(Services::create('render')->render('content_type_edit_page.php',
            [
                'content_type'=>$type,
                'fields_types'=>FieldTypeEnum::getAll(),
                'fields' => $type->getFields(),
                'nids'=>$nids
            ])
        );
    }

    public function deleteContentType(): void
    {
        $type = new NodeType($this->request->get('type'));
        if($type->delete()) {
            Mini::messenger()->addSuccessMessage("Content Type Deleted You can add fields");
            (new RedirectResponse('/admin/content-types'))->send();
        }
    }

    public function newFieldCreation(): void
    {
        $type = new NodeType($this->request->get('type'));
        if($this->request->isMethod('POST')) {

            if($this->request->request->has('field_name') && $this->request->request->has('field_label')
             && $this->request->request->has('field_type')) {

                try{
                    // Creating storage configs
                    $storage = $this->request->request->get('field_name').'_storage';
                    $storage_new = new FieldStorage(null);
                    $storage_new->setStorageName($storage);
                    if(!empty($this->request->request->has('field_multiple_allowed'))) {
                        $is_Allowed = $this->request->request->get('field_multiple_allowed') === 'on';
                        $storage_new->setMultiple($is_Allowed, $this->request->request->get('field_multiple_count'));
                    }

                    if($this->request->request->has('field_empty_allowed')) {
                        $is_empty = $this->request->request->get('field_empty_allowed') === 'on';
                        if($is_empty) {
                            $storage_new->setIsNullable();
                        }
                    }
                    $storage_new->setDefault($this->request->request->get('field_default_value',''));
                    $storage_new->setSize(((int)$this->request->request->get('field_size',0)) === 0 ? 250 : (int)$this->request->request->get('field_size',250));
                    $storage_new->setStorageType(FieldTypeEnum::get($this->request->request->get('field_type', '')));
                    $storage_new->save();

                    $storage = $storage_new->getStorageName();

                    // Create field instance
                    $field_new  =new FieldType(null);
                    $field_new->setLabel($this->request->request->get('field_label'));
                    $field_new->setName($this->request->request->get('field_name'));
                    $field_new->setType(FieldTypeEnum::get($this->request->request->get('field_type', '')));
                    $field_new->setStorage($storage);
                    $field_new->save();

                    // Connect field.
                    $type->setField($field_new->getName());
                    $type->update();
                    Mini::messenger()->addSuccessMessage("Field added to content type successfully");
                }catch (\Throwable $e) {
                    Mini::messenger()->addErrorMessage("Field creation failed check error logs".$e->getMessage());
                    (new ErrorSystem())->setException($e);
                }
            }
        }
        $this->response->write(Services::create('render')->render('content_type_field_form.php',
            [
                'fields_types'=>FieldTypeEnum::getValues()
            ])
        );
    }

    public function fieldUpdate(): void
    {
        $type = new NodeType($this->request->get('type'));
        $field = $type->getField($this->request->get('field_name'));
        if($this->request->getMethod() === 'POST') {
            $field->setLabel($this->request->request->get('field_label'));
            if($field->update()) {
                Mini::messenger()->addSuccessMessage("Field Updated successfully");
                (new RedirectResponse($this->request->headers->get('referer',301)))->send();
                exit;
            }
        }
        $this->response->write(Services::create('render')->render('content_type_field_form_edit.php',
            [
                'fields_types'=>FieldTypeEnum::getValues(),
                'field' => $field,
                'type' => $type,
            ])
        );
    }

    public function deleteField(): void
    {
        $type = new NodeType($this->request->get('type'));
        $field = $type->getField($this->request->get('field_name'));
        if($type->unsetField($field->getName()) && $field->delete()) {
            Mini::messenger()->addSuccessMessage("Field Deleted You can add fields");
        }
        else {
            Mini::messenger()->addErrorMessage("Field Deleted You can add fields");
        }
        (new RedirectResponse($this->request->headers->get('referer',301)))->send();
        exit;
    }
}