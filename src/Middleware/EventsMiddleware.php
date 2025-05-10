<?php

namespace Kwidoo\Lifecycle\Middleware;

use Closure;
use Kwidoo\Lifecycle\Contracts\Strategies\EventStrategy;
use Kwidoo\Lifecycle\Data\LifecycleContextData;
use Kwidoo\Lifecycle\Data\LifecycleData;

class EventsMiddleware
{
    /**
     * @param EventStrategy $eventStrategy
     */
    public function __construct(
        protected EventStrategy $eventStrategy
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
        return $this->eventStrategy->execute($data, fn() => $next($data));
    }
}
