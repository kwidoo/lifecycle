<?php

namespace Kwidoo\Lifecycle\Strategies\Noop;

use Closure;
use Kwidoo\Lifecycle\Contracts\Strategies\LogStrategy;
use Kwidoo\Lifecycle\Data\LifecycleContextData;
use Kwidoo\Lifecycle\Data\LifecycleData;

class NoopLogStrategy implements LogStrategy
{
    /**
     * Execute without logging (no-op)
     *
     * @param LifecycleContextData|LifecycleData $data
     * @param Closure $callback
     * @return mixed
     */
    public function execute(LifecycleContextData|LifecycleData $data, Closure $callback): mixed
    {
        return $callback();
    }

    /**
     * No-op implementation for error logging
     *
     * @param LifecycleContextData|LifecycleData $data
     * @return void
     */
    public function dispatchError(LifecycleContextData|LifecycleData $data): void
    {
        // No-op implementation
    }
}
