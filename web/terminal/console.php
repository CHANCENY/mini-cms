<?php

/**
 * 
 * This is console.php for executing commands
 * 
 * All commands can be registers in module in file
 * YOUR_MODULE.command.yml 
 * 
 * How to register
 * In file YOU_MODULE.command.yml you can list .php files that contains 
 * functions.
 * 
 * How to run command
 * - each function in .php file will be your command eg if you have function
 *   hello() then run comand as hello if incase you have function hello(string $name)
 *   then run hello --name=John Doe NOTE number of args should always much function parameters.
 */

 require_once __DIR__ . '/../../vendor/autoload.php';
 require_once __DIR__ . '/../../core/bootstrap/boot.php';

use Mini\Cms\default\modules\default\terminal\src\Plugin\CommandsLoader;

$kernel = new \Mini\Cms\bootstrap\Kernel();

$kernel->initializeDirectories();

$kernel->initializeApplicationGlobals();


$clean_command = PHP_OS === "Linux" || PHP_OS === 'Darwin' ? 'clear' : 'cls';

system($clean_command);

$command_loader = new CommandsLoader;

$files = $command_loader->getRegisteredCommandScripts();

foreach($files as $file) {
    require_once $file;
}

$colors = CommandsLoader::getColors();

extract($colors);

while(true) {

    try{
        echo PHP_EOL;
        echo $green . "console:" . $reset;
        $input = trim(fgets(STDIN));
        $parsed = $command_loader->parse($input);
        $function = $parsed['command'];
        $options = $parsed['options'];
        if (function_exists($function)) {

            $result = $function(...$options);
            if (!empty($result)) {
                print_r($result);
            }
        }
        echo PHP_EOL;
    }catch(\Throwable $e){
        echo PHP_EOL .$red.'Error: ['.$e->getCode().']'. $e->getMessage(). ' Line: '.$e->getLine(). PHP_EOL;
        echo "File: (".$e->getFile().")".$reset;
    }
    
}
exit(0);