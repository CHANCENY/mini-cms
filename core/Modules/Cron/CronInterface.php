<?php

namespace Mini\Cms\Modules\Cron;

interface CronInterface
{
    /**
     * When to run cron time in minutes. eg 10 ie cron will run at 10 minutes interval.
     * @return int
     */
    public function when(): int;

    /**
     * Cron id
     * @return string
     */
    public function cronId(): string;

    /**
     * Execution of your action code.
     * @return void
     */
    public function execute(): void;
}