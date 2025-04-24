<?php

namespace Kwidoo\Lifecycle\Strategies;

use Kwidoo\Lifecycle\Contracts\Lifecycle\Eventable;
use Kwidoo\Lifecycle\Contracts\Strategies\EventableStrategy;
use Kwidoo\Lifecycle\Data\LifecycleData;

class WithoutEvents implements EventableStrategy
{
    public function __construct(protected Eventable $eventable)
    {
        //
    }

    /**
     * @param string $action
     * @param string $resource
     * @param mixed $context
     * @param callable $callback
     *
     * @return mixed
     */
    public function executeEvents(LifecycleData $data, callable $callback): mixed
    {
        return $callback($data);
    }


    /**
     * @param string $action
     * @param string $resource
     * @param array $context
     *
     * @return void
     */
    public function dispatchError(LifecycleData $data): void
    {
        //
    }
}
