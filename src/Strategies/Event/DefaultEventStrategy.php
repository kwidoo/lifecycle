<?php

namespace Kwidoo\Lifecycle\Strategies\Event;

use Kwidoo\Lifecycle\Contracts\Features\Eventable;
use Kwidoo\Lifecycle\Contracts\Strategies\EventStrategy;
use Kwidoo\Lifecycle\Data\LifecycleContextData;
use Kwidoo\Lifecycle\Data\LifecycleResultData;
use Kwidoo\Lifecycle\Features\Event\EventKeyBuilder;
use Throwable;

class DefaultEventStrategy implements EventStrategy
{
    /**
     * @param Eventable $eventable
     * @param EventKeyBuilder $keyBuilder
     */
    public function __construct(
        protected Eventable $eventable,
        protected EventKeyBuilder $keyBuilder
    ) {}

    /**
     * Execute with event dispatching
     *
     * @param LifecycleContextData $data
     * @param callable $callback
     * @return LifecycleResultData
     */
    public function execute(LifecycleContextData $data, callable $callback): mixed
    {
        $this->dispatchBefore($data);

        /** @var LifecycleResultData */
        $result = $callback();

        $this->dispatchAfter($data, $result);

        return $result;
    }

    /**
     * Dispatch error events
     *
     * @param LifecycleContextData $data
     * @return void
     */
    public function dispatchError(LifecycleContextData $data, Throwable $exception): void
    {
        $this->eventable->dispatchError(
            eventKey: $this->keyBuilder->buildErrorKey($data->action, $data->resource),
            data: $data,
            exception: $exception
        );
    }

    /**
     * Dispatch before events
     *
     * @param LifecycleContextData $data
     * @return void
     */
    protected function dispatchBefore(LifecycleContextData $data): void
    {
        $this->eventable->dispatch(
            $this->keyBuilder->buildBeforeKey($data->action, $data->resource),
            $data->context
        );
    }

    /**
     * Dispatch after events
     *
     * @param LifecycleContextData $data
     * @return void
     */
    protected function dispatchAfter(LifecycleContextData $data, LifecycleResultData $result): void
    {
        $this->eventable->dispatch(
            $this->keyBuilder->buildAfterKey($data->action, $data->resource),
            $data,
            $result->toArray()
        );
    }
}
