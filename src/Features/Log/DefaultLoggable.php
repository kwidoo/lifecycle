<?php

namespace Kwidoo\Lifecycle\Features\Log;

use Psr\Log\LoggerInterface;
use Kwidoo\Lifecycle\Contracts\Features\Loggable;
use Kwidoo\Lifecycle\Data\LifecycleContextData;

class DefaultLoggable implements Loggable
{
    /**
     * @param LoggerInterface $logger
     */
    public function __construct(
        protected LoggerInterface $logger,
    ) {}


    /**
     * Log info message
     *
     * @param string $message
     * @param LifecycleContextData $data
     * @param array $context
     * @return void
     */
    public function logInfo(string $message, LifecycleContextData $data, array $context = []): void
    {
        $this->log(
            level: 'info',
            message: $message,
            context: array_merge(
                $data->toArray(),
                $context
            )
        );
    }

    /**
     * Log error message
     *
     * @param string $message
     * @param LifecycleContextData $data
     * @param array $context
     * @return void
     */
    public function logError(string $message, LifecycleContextData $data, array $context = []): void
    {
        $this->log(
            level: 'error',
            message: $message,
            context: array_merge(
                $data->toArray(),
                $context
            )
        );
    }

    protected function log(string $level, string $message, array $context = []): void
    {
        $this->logger->log(
            level: $level,
            message: $message,
            context: $context
        );
    }
}
