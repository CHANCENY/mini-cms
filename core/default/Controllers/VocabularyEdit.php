<?php

namespace Mini\Cms\default\Controllers;

use Mini\Cms\Controller\ContentType;
use Mini\Cms\Controller\ControllerInterface;
use Mini\Cms\Controller\Request;
use Mini\Cms\Controller\Response;
use Mini\Cms\Controller\StatusCode;
use Mini\Cms\Services\Services;
use Mini\Cms\Vocabulary;
use Symfony\Component\HttpFoundation\RedirectResponse;

class VocabularyEdit implements ControllerInterface
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
            $vocabulary_label = $this->request->getPayload()->get('vocabulary_label');
            $vocabulary_name = $this->request->get('vid');
            if($vocabulary_label) {
                if((new Vocabulary())->load($vocabulary_name)?->update($vocabulary_label)) {
                    (new RedirectResponse('/structure/vocabularies'))->send();
                    exit;
                }
            }
        }
        $this->response->setContentType(ContentType::TEXT_HTML)
            ->setStatusCode(StatusCode::OK)
            ->write(Services::create('render')->render('vocabulary_form_edit.php'));
    }
}