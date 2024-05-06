<?php

namespace Mini\Cms\Controller;

interface ControllerInterface
{

    public function __construct(Request &$request, Response &$response);

    /**
     * This will execute before writeBody.
     * @return bool
     */
    public function isAccessAllowed(): bool;

    /*
     * Writing to response object is way to go no need to return anything.
     */
    public function writeBody(): void;
}