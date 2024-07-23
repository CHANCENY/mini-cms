<?php

namespace Mini\Cms\default\Controllers;

use Mini\Cms\Connections\Database\Database;
use Mini\Cms\Controller\ContentType;
use Mini\Cms\Controller\ControllerInterface;
use Mini\Cms\Controller\Request;
use Mini\Cms\Controller\Response;
use Mini\Cms\Controller\StatusCode;
use Mini\Cms\Services\Services;

class DatabaseProcessMonitor implements ControllerInterface
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
        if($this->request->headers->get('Content-Type') === 'application/json') {
            $processes = (new Database())->dbProcesses();
            $this->response->setContentType(ContentType::APPLICATION_JSON)
                ->setStatusCode(StatusCode::OK)
                ->write($processes);
        }
        else {
            $this->response->write(Services::create('render')->render('database_connection_page_monitor.php'));
        }
    }
}