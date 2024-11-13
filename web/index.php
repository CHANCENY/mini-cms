<?php
require_once '../vendor/autoload.php';
require_once '../core/bootstrap/boot.php';

$kernel = new \Mini\Cms\bootstrap\Kernel();

$kernel->initializeDirectories();

$kernel->initializeApplicationGlobals();

$kernel->initializeApplication();

$kernel->kernelRequestInitialize();

$kernel->appStart();

$kernel->terminate();