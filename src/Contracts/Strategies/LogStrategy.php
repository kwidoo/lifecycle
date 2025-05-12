<?php

namespace Kwidoo\Lifecycle\Contracts\Strategies;

use Kwidoo\Lifecycle\Data\LifecycleContextData;
use Kwidoo\Lifecycle\Data\LifecycleResultData;
use Throwable;

interface LogStrategy
{
    /**
     * Execute with logging capability
     *
     * @param LifecycleContextData $data
     * @param callable $callback
     * @return mixed
     */
    public function execute(LifecycleContextData $data, callable $callback): mixed;

    /**
     * Log error information
     *
     * @param LifecycleContextData $data
     * @param \Throwable|null $exception Optional exception that caused the error
     * @return void
     */
    public function logError(LifecycleContextData $data, ?Throwable $exception = null): void;
}
