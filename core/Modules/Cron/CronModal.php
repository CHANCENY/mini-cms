<?php

namespace Mini\Cms\Modules\Cron;

use Mini\Cms\Modules\Modal\ColumnClassNotFound;
use Mini\Cms\Modules\Modal\Columns\Number;
use Mini\Cms\Modules\Modal\Columns\VarChar;
use Mini\Cms\Modules\Modal\Modal;
use Mini\Cms\Modules\Modal\PrimaryKeyColumnMissing;

class CronModal extends Modal
{

    protected string $main_table = "cron_records";


    /**
     * @throws PrimaryKeyColumnMissing
     * @throws ColumnClassNotFound
     */
    public function __construct()
    {
        $this->columns = array(
            self::buildColumnInstance(Number::class)->name('cron')->parent($this)->size(11)->primary(true)->autoIncrement(true),
            self::buildColumnInstance(VarChar::class)->name('cron_id')->unique(true)->size(50)->parent($this),
            self::buildColumnInstance(VarChar::class)->name('last_run')->size(15)->parent($this),
        );
        parent::__construct();
    }
}