<?php

namespace Kwidoo\Lifecycle\Lifecycle;

use Kwidoo\Lifecycle\Contracts\Lifecycle\LifecycleStrategyResolver;
use Kwidoo\Lifecycle\Data\LifecycleOptionsData;

class DefaultLifecycleStrategyResolver implements LifecycleStrategyResolver
{
    public function __construct(
        /** @var array<bool, EventableStrategy> */
        protected array $eventableStrategies,
        /** @var array<bool, LoggingStrategy> */
        protected array $loggingStrategies,
        /** @var array<bool, TransactionStrategy> */
        protected array $transactionStrategies,
    ) {
    }

    public function resolve(LifecycleOptionsData $options): LifecycleStrategies
    {
        return new LifecycleStrategies(
            eventable: $this->eventableStrategies[$options->eventsEnabled],
            loggable: $this->loggingStrategies[$options->loggingEnabled],
            transactional: $this->transactionStrategies[$options->trxEnabled],
        );
    }
}
