<?php

namespace Kwidoo\Lifecycle\Contracts\Features;

use Kwidoo\Lifecycle\Data\LifecycleContextData;
use Kwidoo\Lifecycle\Data\LifecycleResultData;
use Throwable;

interface ErrorReportable
{
    /**
     * Report an error that occurred during lifecycle execution
     *
     * @param LifecycleContextData $data
     * @param \Throwable $exception
     * @return void
     */
    public function report(LifecycleContextData $data, Throwable $exception): void;
}
