<?php

namespace Mini\Cms\Modules\Terminal;

use Mini\Cms\Modules\Terminal\TerminalInterface;

class CurlRouteImportTerminal implements TerminalInterface
{

    public function __construct(private array $arguments = [])
    {
    }

    public function run(): void
    {
        if(!empty($this->arguments['username']) && !empty($this->arguments['password']) && !empty($this->arguments['hostname'])) {
            $curl = curl_init(trim($this->arguments['hostname'], '/'). '/admin/route-import');
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($this->arguments));
            $response = curl_exec($curl);
            curl_close($curl);
            echo $response;
        }
        else {
            echo 'Please enter username, password and hostname.'.PHP_EOL;
        }
    }
}