<?php

/**
 * This terminal entry.
 */

ini_set('MAX_EXECUTION_TIME', '-1');

require_once '../../vendor/autoload.php';

$commands = \Mini\Cms\Modules\Terminal\TerminalLoader::load();

$system = new \Mini\Cms\System\System();

// Extract all arguments except the script name ($argv[0])
$arguments = array_slice($argv, 1);

if(!empty($arguments) && !empty($commands)) {

    $command = array_shift($arguments);
    $foundListener = $commands[$command];

    if(!empty($foundListener)) {
        $listener = $foundListener['listener'] ?? null;
        if(!empty($listener) && new $listener instanceof \Mini\Cms\Modules\Terminal\TerminalInterface) {
            $options = [];
            if(!empty($arguments)) {
                foreach($arguments as $argument) {
                    $list = explode('=', $argument);
                    $options[trim($list[0], '-')] = trim($list[1]);
                }
            }
            $listener = new $listener($options);
            if($listener instanceof \Mini\Cms\Modules\Terminal\TerminalInterface) {
               exit($listener->run());
            }
        }
    }

}
