<?php

namespace Kwidoo\Lifecycle\Strategies;

use Closure;
use Kwidoo\Lifecycle\Contracts\Strategies\LogStrategy;
use Kwidoo\Lifecycle\Data\LifecycleContextData;
use Kwidoo\Lifecycle\Data\LifecycleData;

/**
 * No-operation implementation of LogStrategy used when logging is disabled
 */
class NoopLogStrategy implements LogStrategy
{
    /**
     * Execute without any logging
     *
     * @param LifecycleContextData|LifecycleData $data
     * @param Closure $callback
     * @return mixed
     */
    public function execute(LifecycleContextData|LifecycleData $data, Closure $callback): mixed
    {
        // Simply execute the callback without any logging
        return $callback();
    }

    /**
     * Do nothing for error logging when logging is disabled
     *
     * @param LifecycleContextData|LifecycleData $data
     * @return void
     */
    public function dispatchError(LifecycleContextData|LifecycleData $data): void
    {
        // Do nothing when logging is disabled
    }
}
