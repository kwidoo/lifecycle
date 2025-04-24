<?php

namespace Kwidoo\Lifecycle\Contracts\Strategies;

use  Kwidoo\Lifecycle\Data\LifecycleData;

interface EventableStrategy
{
    /**
     * @param LifecycleData $data
     * @param callable $callback
     *
     * @return mixed
     */
    public function executeEvents(LifecycleData $data, callable $callback): mixed;

    /**
     * @param LifecycleData $data
     *
     * @return void
     */
    public function dispatchError(LifecycleData $data): void;
}
