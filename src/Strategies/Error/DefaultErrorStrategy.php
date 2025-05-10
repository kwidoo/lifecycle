<?php

namespace Kwidoo\Lifecycle\Strategies\Error;

use Kwidoo\Lifecycle\Contracts\Features\ErrorReportable;
use Kwidoo\Lifecycle\Contracts\Strategies\ErrorStrategy;
use Kwidoo\Lifecycle\Data\LifecycleContextData;
use Kwidoo\Lifecycle\Data\LifecycleData;

class DefaultErrorStrategy implements ErrorStrategy
{
    /**
     * @param ErrorReportable $errorReporter
     */
    public function __construct(
        protected ErrorReportable $errorReporter
    ) {}

    /**
     * Handle an error that occurred during the lifecycle execution
     *
     * @param LifecycleContextData|LifecycleData $data
     * @param \Throwable $error
     * @return mixed
     * @throws \Throwable
     */
    public function handleError(LifecycleContextData|LifecycleData $data, \Throwable $error): mixed
    {
        $this->errorReporter->reportError($data, $error);

        throw $error;
    }
}
