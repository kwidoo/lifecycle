<?php

namespace Kwidoo\Lifecycle\Contracts\Strategies;

use Kwidoo\Lifecycle\Data\LifecycleContextData;
use Kwidoo\Lifecycle\Data\LifecycleData;

interface ErrorStrategy
{
    /**
     * Handle an error that occurred during the lifecycle execution
     *
     * @param LifecycleContextData|LifecycleData $data
     * @param \Throwable $error
     * @return mixed
     * @throws \Throwable
     */
    public function handleError(LifecycleContextData|LifecycleData $data, \Throwable $error): mixed;
}
