<?php

namespace Kwidoo\Lifecycle\Strategies\Event;

use Kwidoo\Lifecycle\Contracts\Features\Eventable;
use Kwidoo\Lifecycle\Contracts\Strategies\EventStrategy;
use Kwidoo\Lifecycle\Data\LifecycleContextData;
use Kwidoo\Lifecycle\Data\LifecycleData;
use Kwidoo\Lifecycle\Data\LifecycleResultData;
use Kwidoo\Lifecycle\Features\Event\EventKeyBuilder;

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
     * @param LifecycleContextData|LifecycleData $data
     * @param \Closure $callback
     * @return mixed
     */
    public function execute(LifecycleContextData|LifecycleData $data, \Closure $callback): mixed
    {
        $this->dispatchBefore($data);

        $result = $callback();

        // Handle assignment of result based on data type
        if ($data instanceof LifecycleData) {
            $data->result = $result;
        }

        $this->dispatchAfter($data);
        return $result;
    }

    /**
     * Dispatch error events
     *
     * @param LifecycleContextData|LifecycleData $data
     * @return void
     */
    public function dispatchError(LifecycleContextData|LifecycleData $data): void
    {
        // Get the result if available (LifecycleData) or null (LifecycleContextData)
        $result = $data instanceof LifecycleData ? $data->result : null;

        $this->eventable->dispatch(
            $this->keyBuilder->buildErrorKey($data->action, $data->resource),
            $result
        );
    }

    /**
     * Dispatch before events
     *
     * @param LifecycleContextData|LifecycleData $data
     * @return void
     */
    protected function dispatchBefore(LifecycleContextData|LifecycleData $data): void
    {
        $this->eventable->dispatch(
            $this->keyBuilder->buildBeforeKey($data->action, $data->resource),
            $data->context
        );
    }

    /**
     * Dispatch after events
     *
     * @param LifecycleContextData|LifecycleData $data
     * @return void
     */
    protected function dispatchAfter(LifecycleContextData|LifecycleData $data): void
    {
        $this->eventable->dispatch(
            $this->keyBuilder->buildAfterKey($data->action, $data->resource),
            $data->context
        );
    }
}
