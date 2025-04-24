<?php

namespace Kwidoo\Lifecycle\Lifecycle;

use Kwidoo\Lifecycle\Contracts\Strategies\EventableStrategy;
use Kwidoo\Lifecycle\Contracts\Strategies\LoggingStrategy;
use Kwidoo\Lifecycle\Contracts\Strategies\TransactionStrategy;

class LifecycleStrategies
{
    public function __construct(
        public EventableStrategy $eventable,
        public TransactionStrategy $transactional,
        public LoggingStrategy $loggable,
    ) {}
}
