<?php

namespace Kwidoo\Lifecycle\Middleware;

use Closure;
use Kwidoo\Lifecycle\Contracts\Strategies\TransactionStrategy;
use Kwidoo\Lifecycle\Data\LifecycleData;

class WithTransactionsMiddleware
{
    public function __construct(
        protected TransactionStrategy $strategy
    ) {
    }

    public function handle(LifecycleData $data, Closure $next): mixed
    {
        return $this->strategy->executeTransactions(fn() => $next($data));
    }
}
