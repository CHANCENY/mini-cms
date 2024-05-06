<?php

namespace Mini\Cms;

use Mini\Cms\StorageManager\Connector;

interface ConnectorInterface
{

    public function connector(Connector $connector);
}