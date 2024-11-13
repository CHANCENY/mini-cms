<?php

namespace Mini\Cms\Modules\PermanentStorage\Connect;

use Mini\Cms\Modules\PermanentStorage\Engine\Engine;
use Mini\Cms\Modules\PermanentStorage\Statement\Delete;
use Mini\Cms\Modules\PermanentStorage\Statement\Select;
use Mini\Cms\Modules\PermanentStorage\Statement\Store;
use Mini\Cms\Modules\PermanentStorage\Statement\Transaction;
use Mini\Cms\Modules\PermanentStorage\Statement\Update;

class Connect extends Engine
{

    /**
     * @var Select Select interface
     */
    public Select $select;

    /**
     * Store interface.
     * @var Store
     */
    public Store $store;

    /**
     * Update interface.
     * @var Update
     */
    public Update $update;

    public Delete $delete;
    public Transaction $transaction;

    /**
     * {@inheritDoc}
     */
    public function __construct(string $database_file_name, string $database_username, string $database_password)
    {
        parent::__construct($database_file_name, $database_username, $database_password);

        $this->select = new Select($this);

        $this->store = new Store($this);

        $this->update = new Update($this);

        $this->delete = new Delete($this);

        $this->transaction = new Transaction($this);
    }
}