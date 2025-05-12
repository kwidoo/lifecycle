<?php

namespace Kwidoo\Lifecycle\Strategies\Log;

use Kwidoo\Lifecycle\Contracts\Factories\LoggableFactory;
use Kwidoo\Lifecycle\Contracts\Strategies\LogStrategy;
use Kwidoo\Lifecycle\Data\LifecycleContextData;
use Kwidoo\Lifecycle\Data\LifecycleResultData;
use Kwidoo\Lifecycle\Features\Log\LogKeyBuilder;
use Throwable;

class DefaultLogStrategy implements LogStrategy
{
    /**
     * @param LoggableFactory $factory
     * @param LogKeyBuilder $keyBuilder
     */
    public function __construct(
        protected LoggableFactory $factory,
        protected LogKeyBuilder $keyBuilder
    ) {}

    /**
     * Execute with logging capability
     *
     * @param LifecycleContextData $data
     * @param callable $callback
     * @return mixed
     */
    public function execute(LifecycleContextData $data, callable $callback): mixed
    {
        $this->logBefore($data);

        $start = microtime(true);

        /** @var LifecycleResultData */
        $result = $callback();

        $duration = microtime(true) - $start;

        $this->logAfter($data, $result, $duration);

        return $result;
    }

    /**
     * Log error information
     *
     * @param LifecycleContextData $data
     * @param \hrowable|null $exception
     * @return void
     */
    public function logError(LifecycleContextData $data, ?Throwable $exception = null): void
    {
        $key = $this->keyBuilder->buildBeforeKey($data->action, $data->resource);

        $loggable = $this->factory->resolve($key);

        $loggable->logError(
            $key,
            $data,
            (array) $exception,
        );
    }

    /**
     * Log the before event
     *
     * @param LifecycleContextData $data
     * @return void
     */
    protected function logBefore(LifecycleContextData $data): void
    {
        $key = $this->keyBuilder->buildBeforeKey($data->action, $data->resource);

        $loggable = $this->factory->resolve($key);
        $loggable->logInfo(
            $key,
            $data,
        );
    }

    /**
     * Log the after event with duration
     *
     * @param LifecycleContextData $data
     * @param float $duration
     * @return void
     */
    protected function logAfter(LifecycleContextData $data, LifecycleResultData $result, float $duration): void
    {
        $key = $this->keyBuilder->buildBeforeKey($data->action, $data->resource);

        $loggable = $this->factory->resolve($key);

        $loggable->logInfo(
            $this->keyBuilder->buildAfterKey($data->action, $data->resource),
            $data,
            [
                'result' => $result->toArray(),
                'duration' => $duration
            ]
        );
    }
}
