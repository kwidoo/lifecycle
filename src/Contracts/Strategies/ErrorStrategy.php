<?php

namespace Kwidoo\Lifecycle\Contracts\Strategies;

use Kwidoo\Lifecycle\Data\LifecycleContextData;
use Throwable;

interface ErrorStrategy
{
    /**
     * Handle an error that occurred during the lifecycle execution
     *
     * @param LifecycleContextData $data
     * @param \Throwable $error
     * @return mixed
     * @throws \Throwable
     */
    public function handleError(LifecycleContextData $data, Throwable $error): mixed;
}
