<?php

namespace Mini\Cms\Connections\Ftp;

use FTP\Connection;
use Mini\Cms\Modules\Storage\Tempstore;

class Ftp extends FtpCredential
{
    private Connection $connection;

    public function __construct()
    {
        parent::__construct();

        // Looking for previous connection made.
        $ftp = Tempstore::load('ftp_connection_object');
        if($ftp) {
            $this->connection = $ftp;
        }

        // Creating new connection if previous connection was not found.
        $server = $this->getFtpServer();
        $list = explode('/', $server);
        $domain = end($list);

        // Lets create possible server urls.
        $possible_server = [
            $domain,
            'https://'.$domain,
            'ftp://'.$domain,
            'http://'.$domain,
        ];
        foreach($possible_server as $server) {

            // Attempt making connection.
            $ftp = ftp_connect($server, (int) $this->getFtpPort(), 30);
            if($ftp) {
                $this->connection = $ftp;
                Tempstore::save('ftp_connection_object', $ftp);
                break;
            }
        }
    }

    /**
     * Get created connection.
     * @return Connection
     */
    public function getConnection(): Connection {
        return $this->connection;
    }

    /**
     * True if login was successful.
     * @return bool
     */
    public function login(): bool
    {
        // Login to FTP server
        return ftp_login($this->connection, $this->getFtpUsername(), $this->getFtpPassword());
    }

}