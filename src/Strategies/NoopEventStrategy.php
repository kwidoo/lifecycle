<?php

namespace Kwidoo\Lifecycle\Strategies;

use Closure;
use Kwidoo\Lifecycle\Contracts\Strategies\EventStrategy;
use Kwidoo\Lifecycle\Data\LifecycleContextData;
use Kwidoo\Lifecycle\Data\LifecycleData;

/**
 * No-operation implementation of EventStrategy used when events are disabled
 */
class NoopEventStrategy implements EventStrategy
{
    /**
     * Execute without triggering any events
     *
     * @param LifecycleContextData|LifecycleData $data
     * @param Closure $callback
     * @return mixed
     */
    public function execute(LifecycleContextData|LifecycleData $data, Closure $callback): mixed
    {
        // Simply execute the callback without any event handling
        return $callback();
    }

    /**
     * Do nothing for error handling when events are disabled
     *
     * @param LifecycleContextData|LifecycleData $data
     * @return void
     */
    public function dispatchError(LifecycleContextData|LifecycleData $data): void
    {
        // Do nothing when events are disabled
    }
}
