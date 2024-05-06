<?php

namespace Mini\Cms\Modules\Terminal;

class TerminalLoader
{
    public static function load(): array {
       $defaults = '../default/default_commands.json';
       $customs = '../../configs/custom_commands.json';

       $commands = [];
       if(file_exists($defaults)) {
          $commands = array_merge($commands, json_decode(file_get_contents($defaults), true) ?? []);
       }
       if(file_exists($customs)) {
          $commands = array_merge($commands, json_decode(file_get_contents($customs), true) ?? []);
       }
       return $commands;
    }
}