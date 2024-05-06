<?php

namespace Mini\Cms\Modules\Terminal;

interface TerminalInterface
{
    public function __construct(array $arguments = []);

    public function run();
}