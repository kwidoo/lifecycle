<?php

namespace Kwidoo\Lifecycle\Contracts\Strategies;

use Kwidoo\Lifecycle\Data\LifecycleContextData;

interface RetryStrategy
{
    /**
     * Execute an operation with retry capability
     *
     * @param LifecycleContextData $data
     * @param callable $callback
     * @return mixed
     */
    public function execute(LifecycleContextData $data, callable $callback): mixed;
}
