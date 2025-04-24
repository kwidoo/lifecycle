<?php

namespace Kwidoo\Lifecycle\Contracts\Strategies;

use  Kwidoo\Lifecycle\Data\LifecycleData;


interface LoggingStrategy
{
    /**
     * @param LifecycleData $data
     * @param callable $callback
     *
     * @return mixed
     */
    public function executeLogging(LifecycleData $data, callable $callback): mixed;

    /**
     * @param LifecycleData $data
     *
     * @return void
     */
    public function dispatchError(LifecycleData $data): void;
}
