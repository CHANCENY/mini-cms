<?php

namespace Mini\Cms\default\Controllers;

use Mini\Cms\Entity;
use Mini\Cms\Services\Services;
use Mini\Cms\Controller\Request;
use Mini\Cms\Controller\Response;
use Mini\Cms\Controller\StatusCode;
use Mini\Cms\Controller\ContentType;
use Mini\Cms\StorageManager\Connector;
use Mini\Cms\Connections\Database\Database;
use Mini\Cms\Controller\ControllerInterface;

class ContentStructuresFieldsListing implements ControllerInterface
{

    public function __construct(private Request &$request,private Response &$response)
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
        $content_type = $this->request->get('content_type_name');
        $entity = Entity::load($content_type);
        $fields = $entity->getEntityFields();
        $this->response->setContentType(ContentType::TEXT_HTML)
            ->setStatusCode(StatusCode::OK)
            ->write(Services::create('render')->render('content_types_fields_listing.php',['entity' => $entity, 'fields' => $fields]));
    }
}