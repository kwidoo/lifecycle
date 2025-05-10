<?php

namespace Kwidoo\Lifecycle\Features\Transaction;

use Closure;
use Illuminate\Database\ConnectionInterface;
use Kwidoo\Lifecycle\Contracts\Features\Transactional;

class DefaultTransactional implements Transactional
{
    /**
     * @param ConnectionInterface $connection
     */
    public function __construct(
        protected ConnectionInterface $connection
    ) {}

    /**
     * Execute a callback within a database transaction
     *
     * @param Closure $callback
     * @return mixed
     */
    public function executeInTransaction(Closure $callback): mixed
    {
        return $this->connection->transaction(fn() => $callback());
    }
}
