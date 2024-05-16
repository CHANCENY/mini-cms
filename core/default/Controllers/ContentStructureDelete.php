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

class ContentStructureDelete implements ControllerInterface
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
        $content_type_name = $this->request->get('content_type_name');
        $action = $this->request->get('action');

        if(empty($action)) {
            $this->response->setContentType(ContentType::TEXT_HTML)
                ->setStatusCode(StatusCode::OK)
                ->write(Services::create('render')->render('confirmation_page.php',['title'=>'Are you sure you want to delete this Content type?']));
        }

        if((int) $action === 1) {
            $entity = Entity::load($content_type_name);
            if($entity->delete()) {
                (new RedirectResponse('/structure/content-type'))->send();
            }
        }

        if ($action !== null && (int) $action === 0) {
            (new RedirectResponse('/structure/content-type'))->send();
        }
    }
}