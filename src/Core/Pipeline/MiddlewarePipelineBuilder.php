<?php

namespace Kwidoo\Lifecycle\Core\Pipeline;

use Illuminate\Contracts\Container\Container;
use Kwidoo\Lifecycle\Contracts\Authorizers\AuthorizerFactory;
use Kwidoo\Lifecycle\Contracts\Strategies\ErrorStrategy;
use Kwidoo\Lifecycle\Contracts\Strategies\EventStrategy;
use Kwidoo\Lifecycle\Contracts\Strategies\LogStrategy;
use Kwidoo\Lifecycle\Contracts\Strategies\RetryStrategy;
use Kwidoo\Lifecycle\Contracts\Strategies\TransactionStrategy;
use Kwidoo\Lifecycle\Data\LifecycleOptionsData;
use Kwidoo\Lifecycle\Middleware\AuthorizationMiddleware;
use Kwidoo\Lifecycle\Middleware\ErrorCatcherMiddleware;
use Kwidoo\Lifecycle\Middleware\EventsMiddleware;
use Kwidoo\Lifecycle\Middleware\FinalizationMiddleware;
use Kwidoo\Lifecycle\Middleware\LoggingMiddleware;
use Kwidoo\Lifecycle\Middleware\RetryMiddleware;
use Kwidoo\Lifecycle\Middleware\TransactionsMiddleware;

class MiddlewarePipelineBuilder
{
    /**
     * @param ErrorStrategy $errorStrategy
     * @param EventStrategy $eventStrategy
     * @param LogStrategy $logStrategy
     * @param RetryStrategy $retryStrategy
     * @param TransactionStrategy $transactionStrategy
     * @param AuthorizerFactory $authorizerFactory
     */
    public function __construct(
        protected ErrorStrategy $errorStrategy,
        protected EventStrategy $eventStrategy,
        protected LogStrategy $logStrategy,
        protected RetryStrategy $retryStrategy,
        protected TransactionStrategy $transactionStrategy,
        protected AuthorizerFactory $authorizerFactory,
    ) {}

    /**
     * Build middleware pipeline based on lifecycle options
     *
     * @param LifecycleOptionsData $options
     * @return array
     */
    public function build(LifecycleOptionsData $options): array
    {
        $middlewares = [];

        // Add ErrorCatcher middleware (always first to catch all errors)
        $middlewares[] = new ErrorCatcherMiddleware($this->errorStrategy);

        // Add RetryMiddleware if retry is enabled
        if ($options->retryEnabled ?? false) {
            $middlewares[] = new RetryMiddleware($this->retryStrategy);
        }

        // Add AuthorizationMiddleware if auth is enabled
        if ($options->authEnabled) {
            $middlewares[] = new AuthorizationMiddleware($this->authorizerFactory);
        }

        // Add TransactionsMiddleware if transactions are enabled
        if ($options->trxEnabled) {
            $middlewares[] = new TransactionsMiddleware($this->transactionStrategy);
        }

        // Add EventsMiddleware if events are enabled
        if ($options->eventsEnabled) {
            $middlewares[] = new EventsMiddleware($this->eventStrategy);
        }

        // Add LoggingMiddleware if logging is enabled
        if ($options->loggingEnabled) {
            $middlewares[] = new LoggingMiddleware($this->logStrategy);
        }

        // Add FinalizationMiddleware (always last to handle cleanup)
        $middlewares[] = new FinalizationMiddleware();

        return $middlewares;
    }

    /**
     * Build a lightweight middleware pipeline for query operations
     * Queries typically need less middleware than commands
     *
     * @param LifecycleOptionsData $options
     * @return array
     */
    public function buildForQueries(LifecycleOptionsData $options): array
    {
        $middlewares = [];

        // Error handling is still necessary
        $middlewares[] = new ErrorCatcherMiddleware($this->errorStrategy);

        // Authorization still applies to queries
        if ($options->authEnabled) {
            $middlewares[] = new AuthorizationMiddleware($this->authorizerFactory);
        }

        // Logging is useful for queries but may have different details
        if ($options->loggingEnabled) {
            $middlewares[] = new LoggingMiddleware($this->logStrategy);
        }

        // Retries might still be useful for queries (e.g., if read model is temporarily unavailable)
        if ($options->retryEnabled) {
            $middlewares[] = new RetryMiddleware($this->retryStrategy);
        }

        // Note: We intentionally exclude:
        // - TransactionsMiddleware (queries are read-only)
        // - EventsMiddleware (queries don't produce domain events)

        // Finalization middleware still applies
        $middlewares[] = new FinalizationMiddleware();

        return $middlewares;
    }
}
