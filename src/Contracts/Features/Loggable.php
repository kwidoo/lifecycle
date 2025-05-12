<?php

namespace Kwidoo\Lifecycle\Contracts\Features;

use Kwidoo\Lifecycle\Data\LifecycleContextData;

interface Loggable
{
    /**
     * Log info message
     *
     * @param string $message
     * @param LifecycleContextData $data
     * @param array $context
     * @return void
     */
    public function logInfo(string $message, LifecycleContextData $data, array $context = []): void;

    /**
     * Log error message
     *
     * @param string $message
     * @param LifecycleContextData $data
     * @param array $context
     * @return void
     */
    public function logError(string $message, LifecycleContextData $data, array $context = []): void;
}
