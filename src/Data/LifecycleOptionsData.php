<?php

namespace Kwidoo\Lifecycle\Data;

use Spatie\LaravelData\Data;

class LifecycleOptionsData extends Data
{
    public function __construct(
        public bool $authEnabled = true,
        public bool $eventsEnabled = true,
        public bool $errorCatcherEnabled = true,
        public bool $trxEnabled = true,
        public bool $loggingEnabled = true,
        public bool $retryEnabled = true,
        public bool $cacheEnabled = true,
        public bool $rateLimitEnabled = true,
        public bool $useCQRS = false,
        public bool $asQuery = false,
    ) {}

    /**
     * Create a new instance with updated properties
     *
     * @param array $parameters
     * @return self
     */
    public function make(...$parameters): self
    {
        return new self(
            authEnabled: $parameters['authEnabled'] ?? $this->authEnabled,
            eventsEnabled: $parameters['eventsEnabled'] ?? $this->eventsEnabled,
            errorCatcherEnabled: $parameters['errorCatcherEnabled'] ?? $this->errorCatcherEnabled,
            trxEnabled: $parameters['trxEnabled'] ?? $this->trxEnabled,
            loggingEnabled: $parameters['loggingEnabled'] ?? $this->loggingEnabled,
            retryEnabled: $parameters['retryEnabled'] ?? $this->retryEnabled,
            cacheEnabled: $parameters['cacheEnabled'] ?? $this->cacheEnabled,
            rateLimitEnabled: $parameters['rateLimitEnabled'] ?? $this->rateLimitEnabled,
            useCQRS: $parameters['useCQRS'] ?? $this->useCQRS,
            asQuery: $parameters['asQuery'] ?? $this->asQuery,
        );
    }

    /**
     * @return self
     */
    public function withoutTrx(): self
    {
        return $this->make(trxEnabled: false);
    }

    /**
     * @return self
     */
    public function withTrx(): self
    {
        return $this->make(trxEnabled: true);
    }

    /**
     * @return self
     */
    public function withoutEvents(): self
    {
        return $this->make(eventsEnabled: false);
    }

    /**
     * @return self
     */
    public function withEvents(): self
    {
        return $this->make(eventsEnabled: true);
    }

    /**
     * @return self
     */
    public function withoutLogging(): self
    {
        return $this->make(loggingEnabled: false);
    }

    /**
     * @return self
     */
    public function withLogging(): self
    {
        return $this->make(loggingEnabled: true);
    }

    /**
     * @return self
     */
    public function withoutAuth(): self
    {
        return $this->make(authEnabled: false);
    }

    /**
     * @return self
     */
    public function withAuth(): self
    {
        return $this->make(authEnabled: true);
    }

    /**
     * @return self
     */
    public function withoutRetry(): self
    {
        return $this->make(retryEnabled: false);
    }

    /**
     * @return self
     */
    public function withRetry(): self
    {
        return $this->make(retryEnabled: true);
    }

    /**
     * @return self
     */
    public function withoutCache(): self
    {
        return $this->make(cacheEnabled: false);
    }

    /**
     * @return self
     */
    public function withCache(): self
    {
        return $this->make(cacheEnabled: true);
    }

    /**
     * @return self
     */
    public function withoutRateLimit(): self
    {
        return $this->make(rateLimitEnabled: false);
    }

    /**
     * @return self
     */
    public function withRateLimit(): self
    {
        return $this->make(rateLimitEnabled: true);
    }

    /**
     * @return self
     */
    public function withoutAll(): self
    {
        return $this->make(
            authEnabled: false,
            eventsEnabled: false,
            errorCatcherEnabled: false,
            trxEnabled: false,
            loggingEnabled: false,
            retryEnabled: false,
            cacheEnabled: false,
            rateLimitEnabled: false
        );
    }

    /**
     * @return self
     * Configure options for API use case (no transactions, with rate limiting)
     */
    public function forApi(): self
    {
        return $this->make(
            trxEnabled: false,
            rateLimitEnabled: true
        );
    }

    /**
     * Enable CQRS command mode for handling commands via aggregates
     * This will route execution through CQRS command handlers and aggregates
     *
     * @param bool $value Whether to enable CQRS command mode
     * @return self
     * @throws \LogicException If trying to enable both CQRS command and query modes
     */
    public function useCQRS(bool $value = true): self
    {
        if ($value && $this->asQuery) {
            throw new \LogicException('Cannot use CQRS command and query modes simultaneously.');
        }

        return $this->make(useCQRS: $value);
    }

    /**
     * Enable CQRS query mode for reading from query models/projections
     * This will bypass command-side middleware and access read models directly
     *
     * @param bool $value Whether to enable query mode
     * @return self
     * @throws \LogicException If trying to enable both CQRS command and query modes
     */
    public function asQuery(bool $value = true): self
    {
        if ($value && $this->useCQRS) {
            throw new \LogicException('Cannot use CQRS command and query modes simultaneously.');
        }

        return $this->make(asQuery: $value);
    }

    /**
     * Convert to array for debug or logging purposes
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'auth' => $this->authEnabled,
            'events' => $this->eventsEnabled,
            'errorCatcher' => $this->errorCatcherEnabled,
            'transactions' => $this->trxEnabled,
            'logging' => $this->loggingEnabled,
            'retry' => $this->retryEnabled,
            'cache' => $this->cacheEnabled,
            'rateLimit' => $this->rateLimitEnabled,
            'useCQRS' => $this->useCQRS,
            'asQuery' => $this->asQuery,
        ];
    }
}
