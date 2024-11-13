<?php

namespace Mini\Cms\default\modules\default\core\src\Controllers;

use Mini\Cms\Controller\ControllerInterface;
use Mini\Cms\Controller\Request;
use Mini\Cms\Controller\Response;
use Mini\Cms\Modules\ErrorSystem;
use Mini\Cms\Services\Services;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ErrorsReport extends ErrorSystem implements ControllerInterface
{

    public function __construct(private Request &$request, private Response &$response)
    {
        parent::__construct();
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
        if($this->request->query->has('error_id')){
            $error = $this->getError($this->request->get('error_id'));
            $this->response->write(Services::create('render')->render('error_details.php',['error'=>$error]));
            return;
        }
        $this->response->write(Services::create('render')->render('errors_report.php',['errors'=>$this->getErrors()]));
    }

    public function clearError(): void
    {
        $this->clear();
        (new RedirectResponse('/reporting/errors'))->send();
        exit;
    }
}