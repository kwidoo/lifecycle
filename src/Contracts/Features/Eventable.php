<?php

namespace Kwidoo\Lifecycle\Contracts\Features;

use Kwidoo\Lifecycle\Data\LifecycleContextData;
use Throwable;

interface Eventable
{
    /**
     * Dispatch an event with the given key and payload
     *
     * @param string $eventKey
     * @param LifecycleContextData $payload
     * @return void
     */
    public function dispatch(string $eventKey, LifecycleContextData $data, array $context = []): void;

    /**
     * @param string $eventKey
     * @param LifecycleContextData $payload
     * @param Throwable $exception
     *
     * @return void
     */
    public function dispatchError(string $eventKey, LifecycleContextData $data, Throwable $exception): void;
}
