<?php

namespace Kwidoo\Lifecycle\Middleware;

use Closure;
use Kwidoo\Lifecycle\Contracts\Strategies\EventableStrategy;
use Kwidoo\Lifecycle\Data\LifecycleData;

class WithEventsMiddleware
{
    public function __construct(
        protected EventableStrategy $strategy
    ) {}

    public function handle(LifecycleData $data, Closure $next): mixed
    {
        return $this->strategy->executeEvents($data, fn() => $next($data));
    }
}
