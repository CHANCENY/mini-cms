<?php

namespace Mini\Cms\default\modules\default\terminal\src\Plugin;

use Exception;
use InvalidArgumentException;
use Mini\Cms\Theme\FileLoader;
use Symfony\Component\Yaml\Yaml;
use Mini\Cms\Modules\Extensions\Extensions;
use Mini\Cms\Modules\Extensions\ModuleHandler\ModuleHandler;

class CommandsLoader
{
    private array $registered_command_scripts = [];

    public function getRegisteredCommandScripts(): array
    {
        return $this->registered_command_scripts;
    }

    public function __construct()
    {
        $active_modules = Extensions::activeModules();
        if (!empty($active_modules)) {
            foreach ($active_modules as $module) {
                if ($module instanceof ModuleHandler) {
                    $module_path = $module->getPath();
                    $script_path = $module_path . DIRECTORY_SEPARATOR . $module->getName() . '.command.yml';
                    if (file_exists($script_path)) {
                        $content = Yaml::parseFile($script_path);
                        if (!empty($content)) {
                            $mini_wrapper = get_global('mini_wrapper_class');
                            foreach ($content as $key => $file) {
                                $found = FileLoader::find($file, $module->getPath());
                                if (!empty($found[0]) && file_exists($found[0])) {
                                    $this->registered_command_scripts[] = $mini_wrapper->getRealPath($found[0]);
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    public static function getColors(): array
    {

        // Extended ANSI escape codes array
        $ansiColors = [
            // Text colors
            "black"        => "\033[30m",
            "red"          => "\033[31m",
            "green"        => "\033[32m",
            "yellow"       => "\033[33m",
            "blue"         => "\033[34m",
            "magenta"      => "\033[35m",
            "cyan"         => "\033[36m",
            "white"        => "\033[37m",
            // Bright text colors
            "bright_black" => "\033[90m",
            "bright_red"   => "\033[91m",
            "bright_green" => "\033[92m",
            "bright_yellow" => "\033[93m",
            "bright_blue"  => "\033[94m",
            "bright_magenta" => "\033[95m",
            "bright_cyan"  => "\033[96m",
            "bright_white" => "\033[97m",

            // Background colors
            "bg_black"     => "\033[40m",
            "bg_red"       => "\033[41m",
            "bg_green"     => "\033[42m",
            "bg_yellow"    => "\033[43m",
            "bg_blue"      => "\033[44m",
            "bg_magenta"   => "\033[45m",
            "bg_cyan"      => "\033[46m",
            "bg_white"     => "\033[47m",
            // Bright background colors
            "bg_bright_black" => "\033[100m",
            "bg_bright_red"   => "\033[101m",
            "bg_bright_green" => "\033[102m",
            "bg_bright_yellow" => "\033[103m",
            "bg_bright_blue"  => "\033[104m",
            "bg_bright_magenta" => "\033[105m",
            "bg_bright_cyan"  => "\033[106m",
            "bg_bright_white" => "\033[107m",

            // Text effects
            "bold"         => "\033[1m",
            "dim"          => "\033[2m",
            "italic"       => "\033[3m",
            "underline"    => "\033[4m",
            "blink"        => "\033[5m",
            "inverse"      => "\033[7m",
            "hidden"       => "\033[8m",
            "strikethrough" => "\033[9m",

            // Reset codes
            "reset"        => "\033[0m",
            "reset_bold"   => "\033[21m",
            "reset_dim"    => "\033[22m",
            "reset_italic" => "\033[23m",
            "reset_underline" => "\033[24m",
            "reset_blink"  => "\033[25m",
            "reset_inverse" => "\033[27m",
            "reset_hidden" => "\033[28m",
            "reset_strikethrough" => "\033[29m",
        ];
        return $ansiColors;
    }

    public function parse(string $line): array
    {
        $result = [
            'command' => '',
            'options' => []
        ];
        $list = str_contains($line, '--') ? explode('--', $line) : $line;

        if (is_array($list)) {
            $result['command'] = trim($list[0]);
            unset($list[0]);
            $options = [];
            foreach ($list as $option) {
                $option_list = explode('=', $option);
                $options[trim($option_list[0])] = trim(end($option_list));
            }
            $result['options'] = $options;
        } else {
            return [
                'command' => $list,
                'options' => []
            ];
        }
        return $result;
    }
}
