<?php

namespace Kwidoo\Lifecycle\Strategies;

use Closure;
use Kwidoo\Lifecycle\Contracts\Strategies\TransactionStrategy;

/**
 * No-operation implementation of TransactionStrategy used when transactions are disabled
 */
class NoopTransactionStrategy implements TransactionStrategy
{
    /**
     * Execute without transaction wrapping
     *
     * @param Closure $callback
     * @return mixed
     */
    public function execute(Closure $callback): mixed
    {
        // Simply execute the callback without any transaction handling
        return $callback();
    }
}
