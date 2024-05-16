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
use Mini\Cms\StorageManager\Connector;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ContentStructureCreation implements ControllerInterface
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
        if($this->request->isMethod(\Symfony\Component\HttpFoundation\Request::METHOD_POST)) {
            $content_label = $this->request->getPayload()->get('content_label');
            $content_name = $this->request->getPayload()->get('content_name');
            $description = $this->request->getPayload()->get('content_description');

            if(!empty($content_name) && !empty($description) && !empty($content_label)) {
                $entity = Entity::create([
                    'entity_type_name' => $content_name,
                    'entity_type_description' => $description,
                    'entity_label' => $content_label,
                ]);

                if($entity->save()) {
                    (new RedirectResponse('/structure/content-type/view/'.$entity->getEntityTypeName()))->send();
                }
            }
        }
        $render = Services::create('render');
       $this->response->setContentType(ContentType::TEXT_HTML)
           ->setStatusCode(StatusCode::OK)
           ->write($render->render('content_types_creation.php'));
    }
}