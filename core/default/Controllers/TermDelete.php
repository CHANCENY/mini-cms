<?php

namespace Mini\Cms\default\Controllers;

use Mini\Cms\Controller\ContentType;
use Mini\Cms\Controller\ControllerInterface;
use Mini\Cms\Controller\Request;
use Mini\Cms\Controller\Response;
use Mini\Cms\Controller\StatusCode;
use Mini\Cms\Entities\Term;
use Mini\Cms\Field;
use Mini\Cms\Services\Services;
use Symfony\Component\HttpFoundation\RedirectResponse;

class TermDelete implements ControllerInterface
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
        $vid = $this->request->get('vid');
        $tid = $this->request->get('tid');

        $action = $this->request->get('action');

        if(empty($action)) {
            $this->response->setContentType(ContentType::TEXT_HTML)
                ->setStatusCode(StatusCode::OK)
                ->write(Services::create('render')->render('confirmation_page.php',['title'=>'Are you sure you want to delete this term?']));
        }

        if((int) $action === 1) {
            $term = Term::term((int) $tid);
            if($term->delete()) {
                (new RedirectResponse('/vocabularies/'.$vid.'/term/list'))->send();
            }
        }

        if ($action !== null && (int) $action === 0) {
            (new RedirectResponse('/vocabularies/'.$vid.'/term/list'))->send();
        }
    }
}