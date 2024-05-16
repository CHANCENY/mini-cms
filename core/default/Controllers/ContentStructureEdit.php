<?php

namespace Mini\Cms\default\Controllers;

use Mini\Cms\Connections\Database\Database;
use Mini\Cms\Controller\ContentType;
use Mini\Cms\Controller\ControllerInterface;
use Mini\Cms\Controller\Request;
use Mini\Cms\Controller\Response;
use Mini\Cms\Controller\StatusCode;
use Mini\Cms\Entity;
use Mini\Cms\Services\Services;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ContentStructureEdit implements ControllerInterface
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
        $content_name = $this->request->get('content_type_name');

        if(empty($content_name)) {
            (new RedirectResponse('/structure/content-types'))->send();
        }

        $entity = Entity::load($content_name);

        if($this->request->isMethod(\Symfony\Component\HttpFoundation\Request::METHOD_POST)) {
            $content_label = $this->request->getPayload()->get('content_label');
            $content_description = $this->request->getPayload()->get('content_description');

            $entity->setEntityLabel($content_label);
            $entity->setEntityTypeDescription($content_description);

            if($entity->update()) {
                (new RedirectResponse('/structure/content-type/view/'.$entity->getEntityTypeName()))->send();
            }
        }

        $this->response->setContentType(ContentType::TEXT_HTML)
            ->setStatusCode(StatusCode::OK)
            ->write(Services::create('render')->render('content_types_editing.php',['entity' => $entity]));
    }
}