<?php

namespace Mini\Cms\Modules\Terminal;

use Mini\Cms\Controller\ControllerInterface;
use Mini\Cms\Controller\Request;
use Mini\Cms\Controller\Response;
use Mini\Cms\Routing\RouteBuilder;

class RouteCreationTerminal implements TerminalInterface
{

    private array $arguments;

    public function __construct(array $arguments = [])
    {
        $this->arguments = $arguments;
    }

    public function run(): int
    {
        $builder = new RouteBuilder();

        $question = [
            "Enter Route Description:" => 'setDescription',
            "Enter Route URL:"=>'setNewUrl',
            "Is Route accessible (Yes or No):" => 'setUnAuthorizedAccess',
            "Give Method Of Access This Route (comma seperated):):" =>'setMethod',
            "Who can access this route? (comma seperated):"=>'setAllowedRole',
            "Give Route Handler (complete namespaced class:):"=>'setHandler',
            "Give Override Route Name:"=>'setName',
        ];

        $builder->setName($this->arguments['name']);
        foreach ($question as $key=>$question) {

            // If no arguments provided, prompt the user for input
            echo $key;
            // Read input from the standard input
            $input = fgets(STDIN);

            // Remove trailing newline character from the input
            $answers = trim($input);

            switch ($question) {
                case 'setNewUrl':
                    $builder->setNewUrl($answers);
                    break;
                case 'setDescription':
                    $builder->setDescription($answers);
                    break;
                case 'setHandler':
                    if(class_exists($answers) && new $answers(Request::createFromGlobals(), new Response()) instanceof ControllerInterface) {
                        $builder->setHandler($answers);
                    }
                    else {
                        throw new \Exception('Controller class not found');
                    }
                    break;
                case 'setUnAuthorizedAccess':
                    $builder->setUnAuthorizedAccess(strtolower($answers) == 'yes');
                    break;
                case 'setMethod':
                    $list = explode(',', $answers);
                    array_filter($list,function ($method) use (&$builder){
                        if(!empty($method)) {
                            $builder->setMethod(strtoupper($method));
                        }
                    });
                    break;
                case 'setAllowedRole':
                    $list = explode(',', $answers);
                    array_filter($list,function ($role) use (&$builder){
                        if(!empty($role)) {
                            $builder->setAllowedRole($role);
                        }
                    });
                    break;
                case 'setName':
                    $builder->setName($answers);
                    break;
            }
        }

        if($builder->save(true)) {
            echo 'Route created successfully.\n\n';
        }else {
            echo "Failed to create route\n\n";
        }
        return 1;
    }
}