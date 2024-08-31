<?php

namespace Mini\Cms\Modules\Authentication;

use Mini\Cms\Routing\Route;
use Symfony\Component\HttpFoundation\Request;

interface AuthenticationInterface
{
    /**
     * Get the theme file name to load for view rendering.
     * @return string
     */
    public function getTheme(): string;

    /**
     * Form submission handler.
     * @param Request $request
     */
    public function authenticate(Request $request): void;

    public function success(Route $success_route): void;

    public function error(Route $error_route): void;

}