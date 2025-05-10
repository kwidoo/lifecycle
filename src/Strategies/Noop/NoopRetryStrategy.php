<?php

namespace Kwidoo\Lifecycle\Strategies\Noop;

use Closure;
use Kwidoo\Lifecycle\Contracts\Strategies\RetryStrategy;
use Kwidoo\Lifecycle\Data\LifecycleContextData;
use Kwidoo\Lifecycle\Data\LifecycleData;

class NoopRetryStrategy implements RetryStrategy
{
    /**
     * Execute without retry capability (no-op)
     *
     * @param LifecycleContextData|LifecycleData $data
     * @param Closure $callback
     * @return mixed
     */
    public function execute(LifecycleContextData|LifecycleData $data, Closure $callback): mixed
    {
        return $callback();
    }
}
