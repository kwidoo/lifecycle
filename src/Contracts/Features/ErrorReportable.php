<?php

namespace Kwidoo\Lifecycle\Contracts\Features;

use Kwidoo\Lifecycle\Data\LifecycleContextData;
use Kwidoo\Lifecycle\Data\LifecycleData;

interface ErrorReportable
{
    /**
     * Report an error that occurred during lifecycle execution
     *
     * @param LifecycleContextData|LifecycleData $data
     * @param \Throwable $error
     * @return void
     */
    public function reportError(LifecycleContextData|LifecycleData $data, \Throwable $error): void;
}
