<?php

namespace Kwidoo\Lifecycle\Middleware;

use Closure;
use Kwidoo\Lifecycle\Contracts\Strategies\ErrorStrategy;
use Kwidoo\Lifecycle\Data\LifecycleContextData;
use Kwidoo\Lifecycle\Data\LifecycleData;

class ErrorCatcherMiddleware
{
    /**
     * @param ErrorStrategy $errorStrategy
     */
    public function __construct(
        protected ErrorStrategy $errorStrategy
    ) {}

    /**
     * Handle the lifecycle request and catch any errors
     *
     * @param LifecycleContextData|LifecycleData $data
     * @param Closure $next
     * @return mixed
     * @throws \Throwable
     */
    public function handle(LifecycleContextData|LifecycleData $data, Closure $next): mixed
    {
        try {
            return $next($data);
        } catch (\Throwable $e) {
            return $this->errorStrategy->handleError($data, $e);
        }
    }
}
