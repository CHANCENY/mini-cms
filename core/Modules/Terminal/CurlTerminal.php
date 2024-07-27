<?php

namespace Mini\Cms\Modules\Terminal;

use Mini\Cms\Modules\Terminal\TerminalInterface;

class CurlTerminal implements TerminalInterface
{

    public function __construct(private array $arguments = [])
    {
    }

    public function run(): void
    {
        if(!empty($this->arguments['url']) && filter_var($this->arguments['url'],FILTER_VALIDATE_URL)) {
            $curl = curl_init($this->arguments['url']);
            curl_exec($curl);
            $error = curl_error($curl);
            curl_close($curl);
            echo $error;
            return;
        }
    }
}