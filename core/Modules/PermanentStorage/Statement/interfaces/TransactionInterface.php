<?php

namespace Mini\Cms\Modules\PermanentStorage\Statement\interfaces;

interface TransactionInterface extends TransactionStatementInterface
{
    public function startTransaction(array $collections_names): void;

    public function commit(): bool;

    public function rollback(): void;

    public function getTransaction(): TransactionStatementInterface;
}