<?php

namespace Kwidoo\Lifecycle\Features\Error;

use Kwidoo\Lifecycle\Contracts\Features\ErrorReportable;
use Kwidoo\Lifecycle\Contracts\Strategies\EventStrategy;
use Kwidoo\Lifecycle\Contracts\Strategies\LogStrategy;
use Kwidoo\Lifecycle\Data\LifecycleContextData;
use Throwable;

class DefaultErrorReportable implements ErrorReportable
{
    /**
     * @param LogStrategy $logStrategy
     * @param EventStrategy $eventStrategy
     */
    public function __construct(
        protected LogStrategy $logStrategy,
        protected EventStrategy $eventStrategy,
    ) {}

    /**
     * Report an error that occurred during lifecycle execution
     *
     * @param LifecycleContextData $data
     * @param Throwable $error
     * @return void
     */
    public function report(LifecycleContextData $data, Throwable $error): void
    {
        // Use LogStrategy to dispatch error
        $this->logStrategy->logError($data, $error);
        $this->eventStrategy->dispatchError($data, $error);
    }
}
