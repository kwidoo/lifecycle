<?php

namespace Kwidoo\Lifecycle\Middleware;

use Closure;
use Kwidoo\Lifecycle\Contracts\Strategies\LogStrategy;
use Kwidoo\Lifecycle\Data\LifecycleContextData;

class LoggingMiddleware
{
    /**
     * Create a new logging middleware instance
     *
     * @param LogStrategy $logStrategy
     */
    public function __construct(
        protected LogStrategy $logStrategy
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
        return $this->logStrategy->execute(
            $data,
            fn() => $next($data)
        );
    }
}
