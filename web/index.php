<?php
require_once '../vendor/autoload.php';

session_start();

/**
 * Loading module and libraries.
 */

use Mini\Cms\Controller\Route;

/**
 * App start here.
 */
$uri = $_SERVER['REQUEST_URI'];
$parse_url = parse_url($uri ?? '/',PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

/**
 * Error handling module.
 */
$error = new \Mini\Cms\Modules\ErrorSystem();
if($error->isOn()) {
    try {
        // Loading app in save mode where error is getting caught.
        Route::app($method, $parse_url);
    }
    catch (Throwable $e) {
        $error->setException($e);
        $error->error();

        if($e instanceof \Mini\Cms\Controller\ControllerErrorInterface) {
            http_response_code($e->getStatusCode());
            header("Content-Type: ".$e->getContentType());
            echo $e->getContent();
        }
        else {
            echo "<p>Unexpected error encountered</p>";
        }
    }
}
else {
    Route::app($method, $parse_url);
}
exit;