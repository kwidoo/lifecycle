<?php

namespace Kwidoo\Lifecycle\Lifecycle;

use Kwidoo\Lifecycle\Strategies\EventableStrategy;
use Kwidoo\Lifecycle\Strategies\LoggingStrategy;
use Kwidoo\Lifecycle\Strategies\TransactionStrategy;

class LifecycleStrategies
{
    public function __construct(
        public EventableStrategy $eventable,
        public TransactionStrategy $transactional,
        public LoggingStrategy $loggable,
    ) {}
}
