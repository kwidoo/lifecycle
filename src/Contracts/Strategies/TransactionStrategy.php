<?php

namespace Kwidoo\Lifecycle\Contracts\Strategies;

use Closure;

interface TransactionStrategy
{
    /**
     * Execute within a transaction
     *
     * @param Closure $callback
     * @return mixed
     */
    public function execute(Closure $callback): mixed;
}
