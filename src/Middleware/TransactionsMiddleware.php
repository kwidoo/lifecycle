<?php

namespace Kwidoo\Lifecycle\Middleware;

use Closure;
use Kwidoo\Lifecycle\Contracts\Strategies\TransactionStrategy;
use Kwidoo\Lifecycle\Data\LifecycleContextData;
use Kwidoo\Lifecycle\Data\LifecycleData;

class TransactionsMiddleware
{
    /**
     * @param TransactionStrategy $transactionStrategy
     */
    public function __construct(
        protected TransactionStrategy $transactionStrategy
    ) {}

    /**
     * Handle the lifecycle request
     *
     * @param LifecycleContextData|LifecycleData $data
     * @param Closure $next
     * @return mixed
     */
    public function handle(LifecycleContextData|LifecycleData $data, Closure $next): mixed
    {
        return $this->transactionStrategy->execute(fn() => $next($data));
    }
}
