<?php

namespace Kwidoo\Lifecycle\Strategies;

use Kwidoo\Lifecycle\Contracts\Lifecycle\Transactional;
use Kwidoo\Lifecycle\Contracts\Strategies\TransactionStrategy;

class WithoutTransactions implements TransactionStrategy
{
    public function __construct(
        protected Transactional $transactional
    ) {
    }

    /**
     * @param callable $callback
     *
     * @return mixed
     */
    public function executeTransactions(callable $callback): mixed
    {
        return $callback();
    }
}
