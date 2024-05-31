<?php

namespace Mini\Cms\default\Controllers;

use Mini\Cms\Controller\ContentType;
use Mini\Cms\Controller\ControllerInterface;
use Mini\Cms\Controller\Request;
use Mini\Cms\Controller\Response;
use Mini\Cms\Controller\StatusCode;
use Mini\Cms\Entities\Term;
use Mini\Cms\Services\Services;
use Symfony\Component\HttpFoundation\RedirectResponse;

class TermCreation implements ControllerInterface
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
            $term_name = $this->request->getPayload()->get('term_name');
            if($term_name) {
                $term = new Term();
                $term->setTerm($term_name);
                $term->setVid($this->request->get('vid'));
                if($term->save()) {
                    (new RedirectResponse("/vocabularies/{$this->request->get('vid')}/term/list"))->send();
                    exit;
                }
            }
        }
        $this->response->setContentType(ContentType::TEXT_HTML)
            ->setStatusCode(StatusCode::OK)
            ->write(Services::create('render')->render('term_creation_form.php'));
    }
}