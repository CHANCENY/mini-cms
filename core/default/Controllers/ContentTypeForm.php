<?php

namespace Mini\Cms\default\Controllers;

use Mini\Cms\Controller\ControllerInterface;
use Mini\Cms\Controller\Request;
use Mini\Cms\Controller\Response;
use Mini\Cms\Mini;
use Mini\Cms\Modules\Content\Field\FieldTypeEnum;
use Mini\Cms\Modules\Content\Node\NodeType;
use Mini\Cms\Modules\Storage\Tempstore;
use Mini\Cms\Services\Services;
use Mini\Cms\Theme\Theme;
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
        $this->response->write(Services::create('render')->render('content_type_edit_page.php',
            [
                'content_type'=>$type,
                'fields_types'=>FieldTypeEnum::getAll(),
                'fields' => $type->getFields(),
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
        $this->response->write(Services::create('render')->render('content_type_field_form.php',
            [
                'fields_types'=>FieldTypeEnum::getValues()
            ])
        );
    }
}