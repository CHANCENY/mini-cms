<?php

namespace Mini\Cms\default\Controllers;

use Mini\Cms\Controller\ContentType;
use Mini\Cms\Controller\ControllerInterface;
use Mini\Cms\Controller\Request;
use Mini\Cms\Controller\Response;
use Mini\Cms\Controller\StatusCode;
use Mini\Cms\Modules\Messenger\Messenger;
use Mini\Cms\Services\Services;
use Mini\Cms\Vocabulary;
use Symfony\Component\HttpFoundation\RedirectResponse;

class VocabularyCreation implements ControllerInterface
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
            $vocabulary_name = $this->request->getPayload()->get('vocabulary_name');

            if($vocabulary_name) {
                if(Vocabulary::create($vocabulary_name)) {
                    (new RedirectResponse('/structure/vocabularies'))->send();
                    exit;
                }
            }
        }
        $this->response->setContentType(ContentType::TEXT_HTML)
            ->setStatusCode(StatusCode::OK)
            ->write(Services::create('render')->render('vocabulary_form.php'));
    }
}