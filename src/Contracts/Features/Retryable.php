<?php

namespace Kwidoo\Lifecycle\Contracts\Features;

use Closure;
use Kwidoo\Lifecycle\Data\LifecycleContextData;
use Kwidoo\Lifecycle\Data\LifecycleData;

interface Retryable
{
    /**
     * Retry a callback on failure
     *
     * @param LifecycleContextData|LifecycleData $data
     * @param Closure $callback
     * @return mixed
     */
    public function retry(LifecycleContextData|LifecycleData $data, Closure $callback): mixed;
}
