<?php

namespace Mini\Cms\default\Controllers;

use Mini\Cms\Controller\ContentType;
use Mini\Cms\Controller\ControllerInterface;
use Mini\Cms\Controller\Request;
use Mini\Cms\Controller\Response;
use Mini\Cms\Controller\StatusCode;
use Mini\Cms\Entity;
use Mini\Cms\Field;
use Mini\Cms\Services\Services;
use Symfony\Component\HttpFoundation\RedirectResponse;

class FieldDelete implements ControllerInterface
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
        $action = $this->request->get('action');

        if(empty($action)) {
            $this->response->setContentType(ContentType::TEXT_HTML)
                ->setStatusCode(StatusCode::OK)
                ->write(Services::create('render')->render('confirmation_page.php',['title'=>'Are you sure you want to delete this field?']));
        }

        if((int) $action === 1) {
            $field = Field::load($field_name);
            if($field->delete()) {
                (new RedirectResponse('/structure/content-type'))->send();
            }
        }

        if ($action !== null && (int) $action === 0) {
            (new RedirectResponse('/structure/content-type'))->send();
        }
    }
}