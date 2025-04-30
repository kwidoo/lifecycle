<?php

namespace Kwidoo\Lifecycle\Middleware;

use Closure;
use Kwidoo\Lifecycle\Contracts\Strategies\LoggingStrategy;
use Kwidoo\Lifecycle\Data\LifecycleData;

class WithLoggingMiddleware
{
    public function __construct(
        protected LoggingStrategy $strategy
    ) {}

    public function handle(LifecycleData $data, Closure $next): mixed
    {
        return $this->strategy->executeLogging($data, fn() => $next($data));
    }
}
