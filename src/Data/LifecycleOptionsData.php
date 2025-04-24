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
    ) {
    }

    /**
     * @return self
     */
    public function withoutTrx(): self
    {
        return new self(
            authEnabled: $this->authEnabled,
            eventsEnabled: $this->eventsEnabled,
            trxEnabled: false,
            loggingEnabled: $this->loggingEnabled,
        );
    }

    /**
     * @return self
     */
    public function withoutEvents(): self
    {
        return new self(
            authEnabled: $this->authEnabled,
            eventsEnabled: false,
            trxEnabled: $this->trxEnabled,
            loggingEnabled: $this->loggingEnabled,
        );
    }

    /**
     * @return self
     */
    public function withoutLogging(): self
    {
        return new self(
            authEnabled: $this->authEnabled,
            eventsEnabled: $this->eventsEnabled,
            trxEnabled: $this->trxEnabled,
            loggingEnabled: false,
        );
    }

    /**
     * @return self
     */
    public function withoutAuth(): self
    {
        return new self(
            authEnabled: false,
            eventsEnabled: $this->eventsEnabled,
            trxEnabled: $this->trxEnabled,
            loggingEnabled: $this->loggingEnabled,
        );
    }

    /**
     * @return self
     */
    public function withoutAll(): self
    {
        return new self(
            authEnabled: false,
            eventsEnabled: false,
            trxEnabled: false,
            loggingEnabled: false,
        );
    }
}
