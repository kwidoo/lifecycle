<?php

namespace Kwidoo\Lifecycle\Data;

use Spatie\LaravelData\Data;

class LifecycleOptionsData extends Data
{
    public function __construct(
        public bool $authEnabled = true,
        public bool $eventsEnabled = true,
        public bool $trxEnabled = true,
        public bool $loggingEnabled = true,
        public bool $retryEnabled = true,
        public bool $cacheEnabled = true,
        public bool $rateLimitEnabled = true,
    ) {}

    /**
     * Create a new instance with updated properties
     *
     * @param array $parameters
     * @return self
     */
    public function copy(...$parameters): self
    {
        return new self(
            authEnabled: $parameters['authEnabled'] ?? $this->authEnabled,
            eventsEnabled: $parameters['eventsEnabled'] ?? $this->eventsEnabled,
            trxEnabled: $parameters['trxEnabled'] ?? $this->trxEnabled,
            loggingEnabled: $parameters['loggingEnabled'] ?? $this->loggingEnabled,
            retryEnabled: $parameters['retryEnabled'] ?? $this->retryEnabled,
            cacheEnabled: $parameters['cacheEnabled'] ?? $this->cacheEnabled,
            rateLimitEnabled: $parameters['rateLimitEnabled'] ?? $this->rateLimitEnabled,
        );
    }

    /**
     * @return self
     */
    public function withoutTrx(): self
    {
        return $this->copy(trxEnabled: false);
    }

    /**
     * @return self
     */
    public function withTrx(): self
    {
        return $this->copy(trxEnabled: true);
    }

    /**
     * @return self
     */
    public function withoutEvents(): self
    {
        return $this->copy(eventsEnabled: false);
    }

    /**
     * @return self
     */
    public function withEvents(): self
    {
        return $this->copy(eventsEnabled: true);
    }

    /**
     * @return self
     */
    public function withoutLogging(): self
    {
        return $this->copy(loggingEnabled: false);
    }

    /**
     * @return self
     */
    public function withLogging(): self
    {
        return $this->copy(loggingEnabled: true);
    }

    /**
     * @return self
     */
    public function withoutAuth(): self
    {
        return $this->copy(authEnabled: false);
    }

    /**
     * @return self
     */
    public function withAuth(): self
    {
        return $this->copy(authEnabled: true);
    }

    /**
     * @return self
     */
    public function withoutRetry(): self
    {
        return $this->copy(retryEnabled: false);
    }

    /**
     * @return self
     */
    public function withRetry(): self
    {
        return $this->copy(retryEnabled: true);
    }

    /**
     * @return self
     */
    public function withoutCache(): self
    {
        return $this->copy(cacheEnabled: false);
    }

    /**
     * @return self
     */
    public function withCache(): self
    {
        return $this->copy(cacheEnabled: true);
    }

    /**
     * @return self
     */
    public function withoutRateLimit(): self
    {
        return $this->copy(rateLimitEnabled: false);
    }

    /**
     * @return self
     */
    public function withRateLimit(): self
    {
        return $this->copy(rateLimitEnabled: true);
    }

    /**
     * @return self
     */
    public function withoutAll(): self
    {
        return $this->copy(
            authEnabled: false,
            eventsEnabled: false,
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
        return $this->copy(
            trxEnabled: false,
            rateLimitEnabled: true
        );
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
            'transactions' => $this->trxEnabled,
            'logging' => $this->loggingEnabled,
            'retry' => $this->retryEnabled,
            'cache' => $this->cacheEnabled,
            'rateLimit' => $this->rateLimitEnabled,
        ];
    }
}
