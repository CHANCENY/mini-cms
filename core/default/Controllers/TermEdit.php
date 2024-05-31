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

class TermEdit implements ControllerInterface
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
        if($this->request->isMethod(\Symfony\Component\HttpFoundation\Request::METHOD_POST)) {
            $term_name = $this->request->get('term_name');
            if($term_name) {
                $term = Term::term($tid);
                $term->setTerm($term_name);
                if($term->update()) {
                    (new RedirectResponse("/vocabularies/$vid/term/list"))->send();
                    exit;
                }
            }
        }
        $term = Term::term((int) $tid);
        $this->response->setContentType(ContentType::TEXT_HTML)
            ->setStatusCode(StatusCode::OK)
            ->write(Services::create('render')->render('term_edit.php', ['tid' => $tid, 'vid' => $vid,'term_name' => $term->getTerm()]));
    }
}