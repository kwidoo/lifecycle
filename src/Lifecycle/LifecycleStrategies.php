<?php

namespace Kwidoo\Lifecycle\Lifecycle;

use Kwidoo\Lifecycle\Contracts\Strategies\ErrorStrategy;
use Kwidoo\Lifecycle\Contracts\Strategies\EventStrategy;
use Kwidoo\Lifecycle\Contracts\Strategies\LogStrategy;
use Kwidoo\Lifecycle\Contracts\Strategies\RetryStrategy;
use Kwidoo\Lifecycle\Contracts\Strategies\TransactionStrategy;

class LifecycleStrategies
{
    public function __construct(
        public EventStrategy $eventable,
        public TransactionStrategy $transactional,
        public LogStrategy $loggable,
        public ?RetryStrategy $retryable = null,
        public ?ErrorStrategy $errorable = null,
    ) {}
}
