<?php

namespace Kwidoo\Lifecycle\Middleware;

use Closure;
use Kwidoo\Lifecycle\Contracts\Strategies\LogStrategy;
use Kwidoo\Lifecycle\Data\LifecycleContextData;
use Kwidoo\Lifecycle\Data\LifecycleData;
use Kwidoo\Lifecycle\Data\LifecycleResultData;

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
     * @param LifecycleContextData|LifecycleData $data
     * @param Closure $next
     * @return mixed
     */
    public function handle(LifecycleContextData|LifecycleData $data, Closure $next): mixed
    {
        // Legacy compatibility
        if ($data instanceof LifecycleData) {
            return $this->logStrategy->execute($data, fn() => $next($data));
        }

        // Get the result data from the next middleware
        $resultData = new LifecycleResultData();
        $updatedResultData = $this->logStrategy->execute(
            $data,
            fn() => $next($data)
        );

        return $updatedResultData;
    }
}
