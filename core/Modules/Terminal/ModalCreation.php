<?php

namespace Mini\Cms\Modules\Terminal;

class ModalCreation implements TerminalInterface
{

    private array $arguments;

    public function __construct(array $arguments = [])
    {
        $this->arguments = $arguments;
    }

    public function run(): void
    {
        $questions = [
            "Give Modal Name:" => null,
            "Where To Create Modal:" => null,
        ];

        // Loop through questions
        foreach ($questions as $question=>&$value) {
            echo $question;
            $input = fgets(STDIN);
            if(!empty($input)) {
                $value = trim($input);
            }
        }

        // Create modal.
        $class =  trim($questions['Give Modal Name:']);
        $location =  trim($questions['Where To Create Modal:']);

        if ($class && $location) {
            $file_name = trim($location,'/') . '/' . $class . '.php';
            if(file_exists($file_name)) {
                echo "<error>File: $file_name already exist.</error>" . PHP_EOL;
                return;
            }

            $content = "<?php".PHP_EOL .PHP_EOL;
            $content .= "class $class extends Modal".PHP_EOL ."{".PHP_EOL.PHP_EOL;
            $content .= "\t"."public function __construct() {".PHP_EOL;
            $content .= PHP_EOL.PHP_EOL."\t\t"."// Override columns and table name here, Note: removing table name not override class name will be use as table name";
            $content .= PHP_EOL.PHP_EOL."\t\tparent::__construct();".PHP_EOL.PHP_EOL."\t"."}";
            $content .= PHP_EOL.PHP_EOL."}";

            file_put_contents($file_name, $content);
            echo PHP_EOL ."<info>Modal created</info>".PHP_EOL;
            echo PHP_EOL ."<info>Note: please add namespace to modal created.</info>".PHP_EOL;
        }
        else {
            echo PHP_EOL . "<error>Class name and modal path are required</error>".PHP_EOL;
        }
    }
}