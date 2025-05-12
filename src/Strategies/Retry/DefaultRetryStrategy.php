<?php

namespace Kwidoo\Lifecycle\Strategies\Retry;

use Kwidoo\Lifecycle\Contracts\Features\Retryable;
use Kwidoo\Lifecycle\Contracts\Strategies\RetryStrategy;
use Kwidoo\Lifecycle\Data\LifecycleContextData;
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
     * @param LifecycleContextData $data
     * @param callable $callback
     * @return LifecycleResultData
     */
    public function execute(LifecycleContextData $data, callable $callback): mixed
    {
        return $this->retryable->retry($data, $callback);
    }
}
