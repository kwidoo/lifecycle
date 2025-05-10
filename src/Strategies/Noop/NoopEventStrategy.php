<?php

namespace Kwidoo\Lifecycle\Strategies\Noop;

use Closure;
use Kwidoo\Lifecycle\Contracts\Strategies\EventStrategy;
use Kwidoo\Lifecycle\Data\LifecycleContextData;
use Kwidoo\Lifecycle\Data\LifecycleData;

class NoopEventStrategy implements EventStrategy
{
    /**
     * Execute without event dispatching (no-op)
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
     * No-op implementation for error events
     *
     * @param LifecycleContextData|LifecycleData $data
     * @return void
     */
    public function dispatchError(LifecycleContextData|LifecycleData $data): void
    {
        // No-op implementation
    }
}
