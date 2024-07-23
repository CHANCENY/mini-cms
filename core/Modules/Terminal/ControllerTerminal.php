<?php

namespace Mini\Cms\Modules\Terminal;

use Mini\Cms\Controller\ControllerInterface;
use Mini\Cms\Controller\Request;
use Mini\Cms\Controller\Response;
use Mini\Cms\Modules\Terminal\TerminalInterface;

class ControllerTerminal implements TerminalInterface
{

    public function __construct(private array $arguments = [])
    {
    }

    public function run(): void
    {
        if(!empty($this->arguments['name']) && class_exists($this->arguments['name'])) {
            $class_name = $this->arguments['name'];
            $request = Request::createFromGlobals();
            $response = new Response();

            $object = new $class_name($request, $response);
            if($object instanceof ControllerInterface && $object->isAccessAllowed()) {
                $object->writeBody();
                if($this->arguments['resp'] == 1) {
                   $response->send();
                }
                else {
                    echo "Controller executed!".PHP_EOL;
                }
            }
        }
        else {
            echo "Failed to execute the controller {$this->arguments['name']}".PHP_EOL;
        }
    }
}