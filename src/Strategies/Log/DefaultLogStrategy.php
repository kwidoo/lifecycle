<?php

namespace Kwidoo\Lifecycle\Strategies\Log;

use Closure;
use Kwidoo\Lifecycle\Contracts\Features\Loggable;
use Kwidoo\Lifecycle\Contracts\Strategies\LogStrategy;
use Kwidoo\Lifecycle\Data\LifecycleContextData;
use Kwidoo\Lifecycle\Data\LifecycleData;
use Kwidoo\Lifecycle\Features\Log\LogKeyBuilder;

class DefaultLogStrategy implements LogStrategy
{
    /**
     * @param Loggable $loggable
     * @param LogKeyBuilder $keyBuilder
     */
    public function __construct(
        protected Loggable $loggable,
        protected LogKeyBuilder $keyBuilder
    ) {}

    /**
     * Execute with logging capability
     *
     * @param LifecycleContextData|LifecycleData $data
     * @param Closure $callback
     * @return mixed
     */
    public function execute(LifecycleContextData|LifecycleData $data, Closure $callback): mixed
    {
        $this->logBefore($data);

        $start = microtime(true);
        $result = $callback();
        $duration = microtime(true) - $start;

        $this->logAfter($data, $duration);

        return $result;
    }

    /**
     * Log error information
     *
     * @param LifecycleContextData|LifecycleData $data
     * @return void
     */
    public function dispatchError(LifecycleContextData|LifecycleData $data): void
    {
        $this->loggable->logError(
            $this->keyBuilder->buildErrorKey($data->action, $data->resource),
            $data
        );
    }

    /**
     * Log the before event
     *
     * @param LifecycleContextData|LifecycleData $data
     * @return void
     */
    protected function logBefore(LifecycleContextData|LifecycleData $data): void
    {
        $this->loggable->logInfo(
            $this->keyBuilder->buildBeforeKey($data->action, $data->resource),
            $data
        );
    }

    /**
     * Log the after event with duration
     *
     * @param LifecycleContextData|LifecycleData $data
     * @param float $duration
     * @return void
     */
    protected function logAfter(LifecycleContextData|LifecycleData $data, float $duration): void
    {
        $this->loggable->logInfo(
            $this->keyBuilder->buildAfterKey($data->action, $data->resource),
            $data,
            ['duration' => $duration]
        );
    }
}
