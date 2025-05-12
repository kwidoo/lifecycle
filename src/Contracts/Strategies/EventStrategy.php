<?php

namespace Kwidoo\Lifecycle\Contracts\Strategies;

use Kwidoo\Lifecycle\Data\LifecycleContextData;
use Throwable;

interface EventStrategy
{
    /**
     * Execute lifecycle events
     *
     * @param LifecycleContextData $data
     * @param callable $callback
     * @return mixed
     */
    public function execute(LifecycleContextData $data, callable $callback): mixed;

    /**
     * Dispatch error events
     *
     * @param LifecycleContextData $data
     * @return void
     */
    public function dispatchError(LifecycleContextData $data, Throwable $exception): void;
}
