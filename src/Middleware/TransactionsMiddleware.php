<?php

namespace Kwidoo\Lifecycle\Middleware;

use Closure;
use Kwidoo\Lifecycle\Contracts\Strategies\TransactionStrategy;
use Kwidoo\Lifecycle\Data\LifecycleContextData;

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
     * @param LifecycleContextData $data
     * @param Closure $next
     * @return mixed
     */
    public function handle(LifecycleContextData $data, Closure $next): mixed
    {
        return $this->transactionStrategy
            ->execute(
                fn() => $next($data)
            );
    }
}
