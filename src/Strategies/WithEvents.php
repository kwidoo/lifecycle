<?php

namespace Kwidoo\Lifecycle\Strategies;

use Kwidoo\Lifecycle\Contracts\Lifecycle\Eventable;
use Kwidoo\Lifecycle\Contracts\Strategies\EventableStrategy;
use Kwidoo\Lifecycle\Data\LifecycleData;

class WithEvents implements EventableStrategy
{
    public function __construct(protected Eventable $eventable) {}

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
        $this->dispatchBefore($data);

        $data->result = $callback();

        $this->dispatchAfter($data);
        return $data->result;
    }

    /**
     * @param string $eventKey
     * @param mixed $context
     *
     * @return void
     */
    protected function dispatchBefore(LifecycleData $data): void
    {
        $this->eventable->dispatch($this->eventKey('before', $data), $data->context);
    }

    /**
     * @param string $eventKey
     * @param mixed $context
     *
     * @return void
     */
    protected function dispatchAfter(LifecycleData $data): void
    {
        $this->eventable->dispatch($this->eventKey('after', $data), $data->context);
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
        $this->eventable->dispatch($this->eventKey('error', $data), $data->result);
    }

    /**
     * @param string $key
     * @param LifecycleData $data
     *
     * @return string
     */
    protected function eventKey(string $key, LifecycleData $data): string
    {
        return "$key.$data->resource.$data->action";
    }
}
