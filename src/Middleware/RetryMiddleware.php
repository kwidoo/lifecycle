<?php

namespace Kwidoo\Lifecycle\Middleware;

use Closure;
use Kwidoo\Lifecycle\Contracts\Strategies\RetryStrategy;
use Kwidoo\Lifecycle\Data\LifecycleContextData;

class RetryMiddleware
{
    /**
     * @param RetryStrategy $retryStrategy
     */
    public function __construct(
        protected RetryStrategy $retryStrategy
    ) {}

    /**
     * Handle the lifecycle request with retry capability
     *
     * @param LifecycleContextData $data
     * @param Closure $next
     * @return mixed
     */
    public function handle(LifecycleContextData $data, Closure $next): mixed
    {
        return $this->retryStrategy
            ->execute(
                $data,
                fn() => $next($data)
            );
    }
}
