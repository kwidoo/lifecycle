<?php

namespace Kwidoo\Lifecycle\Middleware;

use Closure;
use Kwidoo\Lifecycle\Data\LifecycleContextData;
use Kwidoo\Lifecycle\Data\LifecycleData;

class FinalizationMiddleware
{
    /**
     * Handle the lifecycle request and perform finalization tasks
     *
     * @param LifecycleContextData|LifecycleData $data
     * @param Closure $next
     * @return mixed
     */
    public function handle(LifecycleContextData|LifecycleData $data, Closure $next): mixed
    {
        try {
            return $next($data);
        } finally {
            // Execute any cleanup tasks that must always run
            // This middleware should be the last in the pipeline
            $this->cleanup($data);
        }
    }

    /**
     * Perform any cleanup operations
     *
     * @param LifecycleContextData|LifecycleData $data
     * @return void
     */
    protected function cleanup(LifecycleContextData|LifecycleData $data): void
    {
        // Implementation will be added as needed for cleanup tasks
    }
}
