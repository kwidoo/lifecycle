<?php

namespace Kwidoo\Lifecycle\Strategies;


use Kwidoo\Lifecycle\Contracts\Lifecycle\Loggable;
use Kwidoo\Lifecycle\Contracts\Strategies\LoggingStrategy;
use Kwidoo\Lifecycle\Data\LifecycleData;

class WithLogging implements LoggingStrategy
{
    public function __construct(protected Loggable $log)
    {
        // Constructor logic if needed
    }

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

        $this->dispatchBefore($data);

        $data->result = $callback();
        $this->dispatchAfter($data);

        return $data->result;
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
        $this->log->error($this->eventKey('error', $data), [$data->context]);
    }

    /**
     * @param string $eventKey
     * @param array $context
     *
     * @return void
     */
    protected function dispatchBefore(LifecycleData $data): void
    {
        $this->log->info($this->eventKey('before', $data), [$data->context]);
    }

    /**
     * @param string $eventKey
     * @param array $context
     *
     * @return void
     */
    protected function dispatchAfter(LifecycleData $data): void
    {
        $this->log->info($this->eventKey('after', $data), [$data->result]);
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
