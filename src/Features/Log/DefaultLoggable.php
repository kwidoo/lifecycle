<?php

namespace Kwidoo\Lifecycle\Features\Log;

use Psr\Log\LoggerInterface;
use Kwidoo\Lifecycle\Contracts\Features\Loggable;
use Kwidoo\Lifecycle\Data\LifecycleContextData;
use Kwidoo\Lifecycle\Data\LifecycleData;

class DefaultLoggable implements Loggable
{
    /**
     * @param LoggerInterface $logger
     * @param LogKeyBuilder $keyBuilder
     */
    public function __construct(
        protected LoggerInterface $logger,
        protected LogKeyBuilder $keyBuilder
    ) {}

    /**
     * Log info message
     *
     * @param string $message
     * @param LifecycleContextData|LifecycleData $data
     * @param array $context
     * @return void
     */
    public function logInfo(string $message, LifecycleContextData|LifecycleData $data, array $context = []): void
    {
        $this->logger->info($message, array_merge($this->formatDataForContext($data), $context));
    }

    /**
     * Log error message
     *
     * @param string $message
     * @param LifecycleContextData|LifecycleData $data
     * @param array $context
     * @return void
     */
    public function logError(string $message, LifecycleContextData|LifecycleData $data, array $context = []): void
    {
        $this->logger->error($message, array_merge($this->formatDataForContext($data), $context));
    }

    /**
     * Log lifecycle beginning
     *
     * @param string $action
     * @param string $resource
     * @param LifecycleContextData $data
     * @param array $context
     * @return void
     */
    public function logBeforeAction(string $action, string $resource, LifecycleContextData $data, array $context = []): void
    {
        $message = $this->keyBuilder->buildBeforeKey($action, $resource);
        $this->logInfo($message, $data, $context);
    }

    /**
     * Log lifecycle completion
     *
     * @param string $action
     * @param string $resource
     * @param LifecycleData $data
     * @param array $context
     * @return void
     */
    public function logAfterAction(string $action, string $resource, LifecycleData $data, array $context = []): void
    {
        $message = $this->keyBuilder->buildAfterKey($action, $resource);
        $this->logInfo($message, $data, $context);
    }

    /**
     * Log lifecycle error
     *
     * @param string $action
     * @param string $resource
     * @param LifecycleContextData $data
     * @param \Throwable $exception
     * @param array $context
     * @return void
     */
    public function logActionError(string $action, string $resource, LifecycleContextData $data, \Throwable $exception, array $context = []): void
    {
        $message = $this->keyBuilder->buildErrorKey($action, $resource);
        $this->logError($message, $data, array_merge(['exception' => $exception], $context));
    }

    /**
     * Format lifecycle data for context logging
     *
     * @param LifecycleContextData|LifecycleData $data
     * @return array
     */
    protected function formatDataForContext(LifecycleContextData|LifecycleData $data): array
    {
        return [
            'action' => $data->action,
            'resource' => $data->resource,
            'context' => $data->context,
            // Include result if available (from LifecycleData)
            'result' => $data instanceof LifecycleData ? $data->result : null,
        ];
    }
}
