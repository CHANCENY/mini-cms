<?php

namespace Mini\Cms\Controller;

interface ControllerErrorInterface
{
    public function getStatusCode(): int;

    public function getContentType(): string;

    public function getContent(): string;
}