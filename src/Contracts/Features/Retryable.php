<?php

namespace Kwidoo\Lifecycle\Contracts\Features;

use Kwidoo\Lifecycle\Data\LifecycleContextData;
use Kwidoo\Lifecycle\Data\LifecycleResultData;

interface Retryable
{
    /**
     * Retry a callback on failure
     *
     * @param LifecycleContextData $data
     * @param callable $callback
     * @return LifecycleResultData
     */
    public function retry(LifecycleContextData $data, callable $callback): mixed;
}
