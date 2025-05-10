<?php

namespace Kwidoo\Lifecycle\Strategies;

use Closure;
use Kwidoo\Lifecycle\Contracts\Strategies\RetryStrategy;
use Kwidoo\Lifecycle\Data\LifecycleContextData;
use Kwidoo\Lifecycle\Data\LifecycleData;

/**
 * No-operation implementation of RetryStrategy used when retries are disabled
 */
class NoopRetryStrategy implements RetryStrategy
{
    /**
     * Execute without retry capability
     *
     * @param LifecycleContextData|LifecycleData $data
     * @param Closure $callback
     * @return mixed
     */
    public function execute(LifecycleContextData|LifecycleData $data, Closure $callback): mixed
    {
        // Simply execute the callback without any retry handling
        return $callback();
    }
}
