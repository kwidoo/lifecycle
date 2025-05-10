<?php

namespace Kwidoo\Lifecycle\Strategies\Transaction;

use Closure;
use Kwidoo\Lifecycle\Contracts\Features\Transactional;
use Kwidoo\Lifecycle\Contracts\Strategies\TransactionStrategy;

class DefaultTransactionStrategy implements TransactionStrategy
{
    /**
     * @param Transactional $transactional
     */
    public function __construct(
        protected Transactional $transactional
    ) {}

    /**
     * Execute within a transaction
     *
     * @param Closure $callback
     * @return mixed
     */
    public function execute(Closure $callback): mixed
    {
        return $this->transactional->executeInTransaction($callback);
    }
}
