<?php

namespace Kwidoo\Lifecycle\Contracts\Strategies;

use Closure;
use Kwidoo\Lifecycle\Data\LifecycleContextData;
use Kwidoo\Lifecycle\Data\LifecycleData;

interface RetryStrategy
{
    /**
     * Execute an operation with retry capability
     *
     * @param LifecycleContextData|LifecycleData $data
     * @param Closure $callback
     * @return mixed
     */
    public function execute(LifecycleContextData|LifecycleData $data, Closure $callback): mixed;
}
