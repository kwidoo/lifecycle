<?php

namespace Kwidoo\Lifecycle\Middleware;

use Closure;
use Kwidoo\Lifecycle\Data\LifecycleData;
use Kwidoo\Lifecycle\Lifecycle\LifecycleStrategies;

class ErrorCatcherMiddleware
{
    public function __construct(
        protected LifecycleStrategies $strategies
    ) {}

    public function handle(LifecycleData $data, Closure $next): mixed
    {
        try {
            return $next($data);
        } catch (\Throwable $e) {
            $this->strategies->eventable->dispatchError($data);
            $this->strategies->loggable->dispatchError($data);
            throw $e;
        }
    }
}
