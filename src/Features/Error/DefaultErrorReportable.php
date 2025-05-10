<?php

namespace Kwidoo\Lifecycle\Features\Error;

use Illuminate\Contracts\Events\Dispatcher;
use Psr\Log\LoggerInterface;
use Kwidoo\Lifecycle\Contracts\Features\ErrorReportable;
use Kwidoo\Lifecycle\Data\LifecycleContextData;
use Kwidoo\Lifecycle\Data\LifecycleData;
use Kwidoo\Lifecycle\Features\Event\EventKeyBuilder;
use Kwidoo\Lifecycle\Features\Log\LogKeyBuilder;

class DefaultErrorReportable implements ErrorReportable
{
    /**
     * @param LoggerInterface $logger
     * @param Dispatcher $events
     * @param LogKeyBuilder $logKeyBuilder
     * @param EventKeyBuilder $eventKeyBuilder
     */
    public function __construct(
        protected LoggerInterface $logger,
        protected Dispatcher $events,
        protected LogKeyBuilder $logKeyBuilder,
        protected EventKeyBuilder $eventKeyBuilder
    ) {}

    /**
     * Report an error that occurred during lifecycle execution
     *
     * @param LifecycleContextData|LifecycleData $data
     * @param \Throwable $error
     * @return void
     */
    public function reportError(LifecycleContextData|LifecycleData $data, \Throwable $error): void
    {
        // Log the error with context
        $logMessage = $this->logKeyBuilder->buildErrorKey($data->action, $data->resource);
        $this->logger->error(
            $logMessage,
            $this->buildErrorContext($data, $error)
        );

        // Dispatch error event
        $eventKey = $this->eventKeyBuilder->buildErrorKey($data->action, $data->resource);
        $this->events->dispatch($eventKey, [$data, $error]);
    }

    /**
     * Build context information for error logging
     *
     * @param LifecycleContextData|LifecycleData $data
     * @param \Throwable $error
     * @return array
     */
    protected function buildErrorContext(LifecycleContextData|LifecycleData $data, \Throwable $error): array
    {
        $context = [
            'action' => $data->action,
            'resource' => $data->resource,
            'context' => $data->context,
            'exception' => $error,
            'trace' => $error->getTraceAsString(),
        ];

        // Include result data if available (only in legacy LifecycleData)
        if ($data instanceof LifecycleData && isset($data->result)) {
            $context['result'] = $data->result;
        }

        return $context;
    }
}
