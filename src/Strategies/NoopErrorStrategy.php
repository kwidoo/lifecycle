<?php

namespace Kwidoo\Lifecycle\Strategies;

use Throwable;
use Kwidoo\Lifecycle\Contracts\Strategies\ErrorStrategy;
use Kwidoo\Lifecycle\Data\LifecycleContextData;
use Kwidoo\Lifecycle\Data\LifecycleData;

/**
 * No-operation implementation of ErrorStrategy
 */
class NoopErrorStrategy implements ErrorStrategy
{
    /**
     * Handle an error by simply re-throwing it
     *
     * @param LifecycleContextData|LifecycleData $data
     * @param Throwable $error
     * @return mixed
     * @throws Throwable
     */
    public function handleError(LifecycleContextData|LifecycleData $data, Throwable $error): mixed
    {
        // Simply re-throw the error without any additional handling
        throw $error;
    }
}
