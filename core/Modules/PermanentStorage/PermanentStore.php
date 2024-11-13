<?php

namespace Mini\Cms\Modules\PermanentStorage;

use Mini\Cms\Configurations\ConfigFactory;
use Mini\Cms\Modules\PermanentStorage\Connect\Connect;
use Mini\Cms\Services\Services;
use Mini\Cms\System\System;

/**
 * @class Give access to permanent directory storage
 */

class PermanentStore
{
    /**
     * @var Connect Connection.
     */
    private Connect $connect;

    /**
     * Loading configs for connection creation
     * @throws \Exception
     */
    public function __construct()
    {
        /** @var  $permanent_config ConfigFactory*/
        $permanent_config = Services::create('config.factory');
        $config = $permanent_config->get('permanent_storage');

        if($config){
            $dbname = self::dirMapping($config['directory_name']);
            $username = $config['directory_user'];
            $password = $config['directory_password'];
            $this->connect = new Connect($dbname,$username,$password);
        }
    }

    /**
     * Mapping with configs root.
     * @param string $directory_name
     * @return string
     */
    public static function dirMapping(string $directory_name): string
    {
        $system = new System();
        return DIRECTORY_SEPARATOR. trim($system->getAppConfigRoot(), '/') . DIRECTORY_SEPARATOR . $directory_name;
    }

    /**
     * Get Access connection
     * @return Connect
     */
    public function access(): Connect
    {
        return $this->connect;
    }

    /**
     * Connect to directory of permanent storage.
     * @return Connect
     */
    public static function connect(): Connect
    {
        return (new self())->connect;
    }
}