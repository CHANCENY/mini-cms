<?php

namespace Mini\Cms\Connections\Ftp;

use Mini\Cms\Configurations\ConfigFactory;
use Mini\Cms\Services\Services;

class FtpCredential
{

    private string $ftp_server;
    private string $ftp_username;
    private string $ftp_password;
    private string $ftp_port;
    private string $ftp_root;

    public function getFtpServer(): string
    {
        return $this->ftp_server;
    }

    public function getFtpUsername(): string
    {
        return $this->ftp_username;
    }

    public function getFtpPassword(): string
    {
        return $this->ftp_password;
    }

    public function getFtpPort(): string
    {
        return $this->ftp_port;
    }

    public function getFtpRoot(): string
    {
        return $this->ftp_root;
    }


    public function __construct()
    {
        $config = Services::create('config.factory');
        if($config instanceof ConfigFactory) {
            $ftp = $config->get('ftp_connection') ?? [];
            $this->ftp_server = $ftp['ftp_server'] ?? 'null';
            $this->ftp_username = $ftp['ftp_username'] ?? 'null';
            $this->ftp_password = $ftp['ftp_password'] ?? 'null';
            $this->ftp_port = $ftp['ftp_port'] ?? 'null';
            $this->ftp_root = $ftp['ftp_root'] ?? 'null';
        }
    }
}