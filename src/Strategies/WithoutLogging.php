<?php

namespace Kwidoo\Lifecycle\Strategies;

use Kwidoo\Lifecycle\Contracts\Lifecycle\Loggable;
use Kwidoo\Lifecycle\Contracts\Strategies\LoggingStrategy;
use Kwidoo\Lifecycle\Data\LifecycleData;

class WithoutLogging implements LoggingStrategy
{
    /**
     * @param string $action
     * @param string $resource
     * @param mixed $context
     * @param callable $callback
     *
     * @return mixed
     */
    public function executeLogging(LifecycleData $data, callable $callback): mixed
    {
        return $callback();
    }

    /**
     * @param string $action
     * @param string $resource
     * @param mixed $context
     *
     * @return void
     */
    public function dispatchError(LifecycleData $data): void
    {
        //
    }
}
