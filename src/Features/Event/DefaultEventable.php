<?php

namespace Kwidoo\Lifecycle\Features\Event;

use Kwidoo\Lifecycle\Contracts\Features\Eventable;
use Illuminate\Contracts\Events\Dispatcher;

class DefaultEventable implements Eventable
{
    /**
     * @param Dispatcher $events
     * @param EventKeyBuilder $keyBuilder
     */
    public function __construct(
        protected Dispatcher $events,
        protected EventKeyBuilder $keyBuilder
    ) {}

    /**
     * Dispatch an event with the given key and payload
     *
     * @param string $eventKey
     * @param mixed $payload
     * @return void
     */
    public function dispatch(string $eventKey, mixed $payload): void
    {
        $this->events->dispatch($eventKey, $payload);
    }

    /**
     * Dispatch a 'before' event for the given action and resource
     *
     * @param string $action
     * @param string $resource
     * @param mixed $payload
     * @return void
     */
    public function dispatchBeforeEvent(string $action, string $resource, mixed $payload): void
    {
        $eventKey = $this->keyBuilder->buildBeforeKey($action, $resource);
        $this->dispatch($eventKey, $payload);
    }

    /**
     * Dispatch an 'after' event for the given action and resource
     *
     * @param string $action
     * @param string $resource
     * @param mixed $payload
     * @return void
     */
    public function dispatchAfterEvent(string $action, string $resource, mixed $payload): void
    {
        $eventKey = $this->keyBuilder->buildAfterKey($action, $resource);
        $this->dispatch($eventKey, $payload);
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
    public function dispatchErrorEvent(string $action, string $resource, mixed $payload, \Throwable $exception): void
    {
        $eventKey = $this->keyBuilder->buildErrorKey($action, $resource);
        $this->dispatch($eventKey, ['payload' => $payload, 'exception' => $exception]);
    }
}
