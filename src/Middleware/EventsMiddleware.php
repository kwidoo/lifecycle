<?php

namespace Kwidoo\Lifecycle\Middleware;

use Closure;
use Kwidoo\Lifecycle\Contracts\Strategies\EventStrategy;
use Kwidoo\Lifecycle\Data\LifecycleContextData;
use Kwidoo\Lifecycle\Data\LifecycleResultData;

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
     * @param LifecycleContextData $data
     * @param Closure $next
     * @return LifecycleResultData
     */
    public function handle(LifecycleContextData $data, Closure $next): mixed
    {
        return $this->eventStrategy
            ->execute(
                $data,
                fn() => $next($data)
            );
    }
}
