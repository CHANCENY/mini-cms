<?php

namespace Mini\Cms\default\Controllers;

use Mini\Cms\Controller\ContentType;
use Mini\Cms\Controller\ControllerInterface;
use Mini\Cms\Controller\Request;
use Mini\Cms\Controller\Response;
use Mini\Cms\Controller\StatusCode;
use Mini\Cms\Entities\Term;
use Mini\Cms\Services\Services;

class ContentByTerm implements ControllerInterface
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
        $tid = $this->request->get('tid');
        $nodes = Term::find($tid);
        $this->response->setContentType(ContentType::TEXT_HTML)
            ->setStatusCode(StatusCode::OK)
            ->write(Services::create('render')->render('term_view_content.php',['nodes'=>$nodes]));
    }
}