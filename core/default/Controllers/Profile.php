<?php

namespace Mini\Cms\default\Controllers;

use Mini\Cms\Controller\ControllerInterface;
use Mini\Cms\Controller\Request;
use Mini\Cms\Controller\Response;
use Mini\Cms\Modules\CurrentUser\CurrentUser;
use Symfony\Component\HttpFoundation\RedirectResponse;

class Profile implements ControllerInterface
{

    public function __construct(Request &$request, Response &$response)
    {
    }

    /**
     * @inheritDoc
     */
    public function isAccessAllowed(): bool
    {
        return true;
    }

    public function writeBody(): void
    {
        $currentUser = new CurrentUser();
        if($currentUser->id()) {
            (new RedirectResponse('/user/'.$currentUser->id()))->send();
            exit;
        }

        (new RedirectResponse('/'))->send();
    }
}