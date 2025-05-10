<?php

namespace Kwidoo\Lifecycle\Contracts\Features;

use Kwidoo\Lifecycle\Data\LifecycleContextData;
use Kwidoo\Lifecycle\Data\LifecycleData;

interface Loggable
{
    /**
     * Log info message
     *
     * @param string $message
     * @param LifecycleContextData|LifecycleData $data
     * @param array $context
     * @return void
     */
    public function logInfo(string $message, LifecycleContextData|LifecycleData $data, array $context = []): void;

    /**
     * Log error message
     *
     * @param string $message
     * @param LifecycleContextData|LifecycleData $data
     * @param array $context
     * @return void
     */
    public function logError(string $message, LifecycleContextData|LifecycleData $data, array $context = []): void;
}
