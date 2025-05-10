<?php

namespace Kwidoo\Lifecycle\Contracts\Strategies;

use Closure;
use Kwidoo\Lifecycle\Data\LifecycleContextData;
use Kwidoo\Lifecycle\Data\LifecycleData;

interface EventStrategy
{
    /**
     * Execute lifecycle events
     *
     * @param LifecycleContextData|LifecycleData $data
     * @param Closure $callback
     * @return mixed
     */
    public function execute(LifecycleContextData|LifecycleData $data, Closure $callback): mixed;

    /**
     * Dispatch error events
     *
     * @param LifecycleContextData|LifecycleData $data
     * @return void
     */
    public function dispatchError(LifecycleContextData|LifecycleData $data): void;
}
