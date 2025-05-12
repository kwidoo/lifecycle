<?php

namespace Kwidoo\Lifecycle\Middleware;

use Closure;
use Kwidoo\Lifecycle\Contracts\Strategies\ErrorStrategy;
use Kwidoo\Lifecycle\Data\LifecycleContextData;
use Kwidoo\Lifecycle\Data\LifecycleResultData;
use Throwable;

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
     * @param LifecycleContextData $data
     * @param Closure $next
     * @return LifecycleResultData
     * @throws \Throwable
     */
    public function handle(LifecycleContextData $data, Closure $next): mixed
    {
        try {
            return $next($data);
        } catch (Throwable $e) {
            return $this->errorStrategy
                ->handleError($data, $e);
        }
    }
}
