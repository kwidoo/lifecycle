<?php

namespace Kwidoo\Lifecycle\Contracts\Strategies;

interface TransactionStrategy
{
    /**
     * Execute within a transaction
     *
     * @param callable $callback
     * @return mixed
     */
    public function execute(callable $callback): mixed;
}
