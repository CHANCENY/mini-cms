<?php

namespace Mini\Cms\default\modules\default\todo\src\Controllers;

use Mini\Cms\Controller\ContentType;
use Mini\Cms\Controller\ControllerInterface;
use Mini\Cms\Controller\Request;
use Mini\Cms\Controller\Response;
use Mini\Cms\Controller\StatusCode;
use Mini\Cms\Services\Services;
use Mini\Cms\Theme\Theme;

class Todo implements ControllerInterface
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
        $tasks = \Mini\Cms\default\modules\default\todo\src\Plugin\Todo::getTasks();
       $this->response->write(Services::create('render')->render('todo-page-view.php',
       [
           'tasks' => $tasks,
       ]));
    }

    public function actionsController(): void
    {
        if ($this->request->isMethod('POST')) {
            $content = json_decode($this->request->getContent());
            $task = $content->task;
            $time = $content->time;
            $timestamp = strtotime($time);
            if($task_id = \Mini\Cms\default\modules\default\todo\src\Plugin\Todo::create($task, $timestamp)) {

                $this->response->setContentType(ContentType::APPLICATION_JSON)
                    ->setStatusCode(StatusCode::CREATED)
                    ->write(['status' => StatusCode::CREATED, 'data'=> Theme::build('task-row-view.php', ['task' =>
                    \Mini\Cms\default\modules\default\todo\src\Plugin\Todo::get($task_id)])]);
            }
            else {
                $this->response->setContentType(ContentType::APPLICATION_JSON)
                    ->setStatusCode(StatusCode::NOT_ACCEPTABLE)
                    ->write(['status' => StatusCode::NOT_ACCEPTABLE]);
            }
        }
        else {
            $this->response->setContentType(ContentType::APPLICATION_JSON)
                ->setStatusCode(StatusCode::METHOD_NOT_ALLOWED)
                ->write(['status' => StatusCode::METHOD_NOT_ALLOWED]);
        }
    }

    public function deleteController(): void
    {
       $task_id = $this->request->get('task_id');
       if($task_id) {
           if(\Mini\Cms\default\modules\default\todo\src\Plugin\Todo::delete($task_id)) {
               $this->response->setContentType(ContentType::APPLICATION_JSON)
                   ->setStatusCode(StatusCode::OK)
                   ->write(['status' => StatusCode::OK]);
           }
           else {
               $this->response->setContentType(ContentType::APPLICATION_JSON)
                   ->setStatusCode(StatusCode::NOT_ACCEPTABLE)
                   ->write(['status' => StatusCode::NOT_ACCEPTABLE]);
           }
       }else {
           $this->response->setContentType(ContentType::APPLICATION_JSON)
               ->setStatusCode(StatusCode::NOT_ACCEPTABLE)
               ->write(['status' => StatusCode::NOT_ACCEPTABLE]);
       }
    }

    public function bulkDeleteController(): void
    {
        if($this->request->isMethod('DELETE')) {
            $tasks = json_decode($this->request->getContent(),true);
            if(!empty($tasks['data'])) {
                if(\Mini\Cms\default\modules\default\todo\src\Plugin\Todo::bulkDelete($tasks['data'])) {
                    $this->response->setContentType(ContentType::APPLICATION_JSON)
                        ->setStatusCode(StatusCode::OK)
                        ->write(['status' => StatusCode::OK]);
                }
                else {
                    $this->response->setContentType(ContentType::APPLICATION_JSON)
                        ->setStatusCode(StatusCode::NOT_ACCEPTABLE)
                        ->write(['status' => StatusCode::NOT_ACCEPTABLE]);
                }
            }
            else {
                $this->response->setContentType(ContentType::APPLICATION_JSON)
                    ->setStatusCode(StatusCode::NOT_ACCEPTABLE)
                    ->write(['status' => StatusCode::NOT_ACCEPTABLE]);
            }
        }
        else {
            $this->response->setContentType(ContentType::APPLICATION_JSON)
                ->setStatusCode(StatusCode::METHOD_NOT_ALLOWED)
                ->write(['status' => StatusCode::METHOD_NOT_ALLOWED]);
        }
    }
}