<?php

namespace Mini\Cms\default\modules\default\inbox\src\Services;

use Mini\Cms\Connections\Imap\ImapServer;

class ImapReader extends ImapServer
{
    public function __construct()
    {
        parent::__construct();
    }
}