<?php

namespace Kwidoo\Lifecycle\Features\Event;

use Kwidoo\Lifecycle\Contracts\Features\Eventable;
use Illuminate\Contracts\Events\Dispatcher;
use Kwidoo\Lifecycle\Data\LifecycleContextData;
use Throwable;

class DefaultEventable implements Eventable
{
    /**
     * @param Dispatcher $events
     * @param EventKeyBuilder $keyBuilder
     */
    public function __construct(
        protected Dispatcher $events,
    ) {}

    /**
     * Dispatch an event with the given key and payload
     *
     * @param string $eventKey
     * @param LifecycleContextData $payload
     * @return void
     */
    public function dispatch(string $eventKey, LifecycleContextData $data, array $context = []): void
    {
        $this->events->dispatch(
            $eventKey,
            payload: array_merge(
                $data->toArray(),
                $context
            ),
        );
    }

    /**
     * Dispatch an 'error' event for the given action and resource
     *
     * @param string $action
     * @param string $resource
     * @param mixed $payload
     * @param \Throwable $exception
     * @return void
     */
    public function dispatchError(string $eventKey, LifecycleContextData $data, Throwable $exception): void
    {
        $this->dispatch(
            eventKey: $eventKey,
            data: $data,
            context: (array) $exception
        );
    }
}
