<?php

namespace Kwidoo\Lifecycle\Strategies\Retry;

use Closure;
use Kwidoo\Lifecycle\Contracts\Features\Retryable;
use Kwidoo\Lifecycle\Contracts\Strategies\RetryStrategy;
use Kwidoo\Lifecycle\Data\LifecycleContextData;
use Kwidoo\Lifecycle\Data\LifecycleData;
use Kwidoo\Lifecycle\Data\LifecycleResultData;

class DefaultRetryStrategy implements RetryStrategy
{
    /**
     * @param Retryable $retryable
     */
    public function __construct(
        protected Retryable $retryable
    ) {}

    /**
     * Execute an operation with retry capability
     *
     * @param LifecycleContextData|LifecycleData $data
     * @param Closure $callback
     * @return mixed
     */
    public function execute(LifecycleContextData|LifecycleData $data, Closure $callback): mixed
    {
        return $this->retryable->retry($data, $callback);
    }
}
