<?php

namespace Kwidoo\Lifecycle\Strategies;

use Closure;
use Kwidoo\Lifecycle\Contracts\Lifecycle\Transactional;
use Kwidoo\Lifecycle\Contracts\Strategies\TransactionStrategy;

class WithTransactions implements TransactionStrategy
{
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
        return $this->transactional->run($callback);
    }

    /**
     * @deprecated Use execute() instead
     * @param callable $callback
     *
     * @return mixed
     */
    public function executeTransactions(callable $callback): mixed
    {
        return $this->execute($callback);
    }
}
