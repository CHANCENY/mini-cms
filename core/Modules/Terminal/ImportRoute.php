<?php

namespace Mini\Cms\Modules\Terminal;

use Mini\Cms\Modules\Extensions\ModuleHandler\ModuleHandler;
use Mini\Cms\Modules\Streams\MiniWrapper;
use Mini\Cms\Modules\Terminal\TerminalInterface;
use Mini\Cms\System\System;
use Mini\Cms\Theme\FileLoader;

class ImportRoute implements TerminalInterface
{

    public function __construct(private array $arguments = [])
    {
    }

    public function run()
    {
        $system = new System();
        if(!empty($this->arguments['from'])) {
            $module = new ModuleHandler($this->arguments['from']);
            if($module->getName()) {
                $path = $module->getPath();
                $routes = $system->getAppWebRoot() .'/'. trim((new MiniWrapper())->getRealPath($path), '/') .'/'.'route.import.json';
                if(file_exists($routes)) {
                    $routes_data = json_decode(file_get_contents($routes), true);
                    if(!empty($routes_data)) {

                        $customs = (new FileLoader($system->getAppConfigRoot()))->findFiles('custom_routes.json')[0] ?? '';
                        if(!file_exists($customs)) {
                            echo "Error: ($customs) not found.".PHP_EOL;
                            return;
                        }

                        $custom_routes = json_decode(file_get_contents($customs), true);
                        foreach ($routes_data as $route) {
                            $matched = array_filter($custom_routes, function ($r) use ($route){
                                return trim($route['url']) === trim($r['url']);
                            });
                            if(empty($matched)) {
                                echo "STATUS: Adding route ({$route['name']})".PHP_EOL;
                                $custom_routes[] = $route;
                            }else {
                                echo "STATUS: Route exist already ({$route['name']})". PHP_EOL;
                            }
                        }

                        if(file_put_contents($customs, json_encode($custom_routes,JSON_PRETTY_PRINT))) {
                            echo "Imported successfully".PHP_EOL;
                        }
                        else {
                            echo "Error: Import failed". PHP_EOL;
                        }
                    }
                }
                else {
                    echo "File ($routes) not found".PHP_EOL;
                }
            }
        }

        else {
            echo "ARG: --from is missing (module name)".PHP_EOL;
        }
    }
}