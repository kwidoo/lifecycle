<?php

namespace Kwidoo\Lifecycle\Strategies\Noop;

use Closure;
use Kwidoo\Lifecycle\Contracts\Strategies\TransactionStrategy;

class NoopTransactionStrategy implements TransactionStrategy
{
    /**
     * Execute without transaction (no-op)
     *
     * @param Closure $callback
     * @return mixed
     */
    public function execute(Closure $callback): mixed
    {
        return $callback();
    }
}
