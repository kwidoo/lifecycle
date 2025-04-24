<?php

namespace Kwidoo\Lifecycle\Contracts\Strategies;

interface TransactionStrategy
{
    /**
     * @param callable $callback
     *
     * @return mixed
     */
    public function executeTransactions(callable $callback): mixed;
}
